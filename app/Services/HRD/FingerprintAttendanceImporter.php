<?php

namespace App\Services\HRD;

use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\FingerprintImport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Turns a fingerprint machine log into staff attendance (FASE 119.6.1).
 *
 * The machine writes one line per scan. For each employee and day the first
 * scan is the check-in and the last the check-out, merged with what is
 * already stored, so importing the same file again (or an overlapping one)
 * changes nothing. Days already recorded as leave (izin, sakit, cuti) are left
 * alone. Machine IDs no employee is mapped to, and lines that cannot be read,
 * are reported rather than dropped.
 */
class FingerprintAttendanceImporter
{
    public const LEAVE_STATUSES = ['izin', 'sakit', 'cuti'];

    private const FORMATS = ['Y-m-d H:i:s', 'Y-m-d H:i', 'd/m/Y H:i:s', 'd/m/Y H:i', 'd-m-Y H:i:s', 'd-m-Y H:i', 'Y/m/d H:i:s', 'Y/m/d H:i'];

    public function import(string $path, string $originalName, ?User $importer): FingerprintImport
    {
        $rows = $this->readRows($path, $originalName);
        $hasHeader = (bool) config('fingerprint.has_header');
        $header = $hasHeader ? array_shift($rows) ?? [] : [];
        $columns = $this->resolveColumns($header, $hasHeader);

        /** @var array<string, list<Carbon>> $scans */
        $scans = [];
        $invalid = [];
        $read = 0;
        foreach ($rows as $index => $row) {
            if ($this->isBlank($row)) {
                continue;
            }
            $read++;
            $line = $index + ($hasHeader ? 2 : 1);
            $machineId = trim((string) ($row[$columns['machine_id']] ?? ''));
            $moment = $this->parseMoment($row, $columns);
            if ($machineId === '' || $moment === null) {
                $invalid[] = ['line' => $line, 'reason' => $machineId === '' ? 'ID mesin kosong' : 'Tanggal/jam tidak terbaca'];

                continue;
            }
            $scans[$machineId][] = $moment;
        }

        $employees = Employee::whereIn('fingerprint_id', array_map('strval', array_keys($scans)))->get()->keyBy('fingerprint_id');

        $unknown = [];
        $days = [];
        $matched = 0;
        $allMoments = [];
        foreach ($scans as $machineId => $moments) {
            $machineId = (string) $machineId;
            $allMoments = [...$allMoments, ...$moments];
            $employee = $employees->get($machineId);
            if (! $employee) {
                $unknown[$machineId] = count($moments);

                continue;
            }
            $matched += count($moments);
            foreach ($moments as $moment) {
                $date = $moment->toDateString();
                $time = $moment->format('H:i:s');
                $day = $days[$employee->id][$date] ?? ['in' => $time, 'out' => $time];
                $days[$employee->id][$date] = ['in' => min($day['in'], $time), 'out' => max($day['out'], $time)];
            }
        }

        $stats = ['attendances_created' => 0, 'attendances_updated' => 0, 'attendances_unchanged' => 0, 'leave_days_kept' => 0];
        DB::transaction(function () use ($days, &$stats) {
            foreach ($days as $employeeId => $dates) {
                foreach ($dates as $date => $day) {
                    $stats[$this->storeDay($employeeId, $date, $day['in'], $day['out'])]++;
                }
            }
        });

        usort($allMoments, fn (Carbon $a, Carbon $b) => $a <=> $b);

        return FingerprintImport::create([
            'original_name' => $originalName,
            'imported_by' => $importer?->id,
            'rows_read' => $read,
            'scans_matched' => $matched,
            ...$stats,
            'unknown_ids' => $unknown ?: null,
            'invalid_rows' => $invalid ?: null,
            'first_date' => $allMoments ? $allMoments[0]->toDateString() : null,
            'last_date' => $allMoments ? end($allMoments)->toDateString() : null,
        ]);
    }

    /** @return 'attendances_created'|'attendances_updated'|'attendances_unchanged'|'leave_days_kept' */
    private function storeDay(int $employeeId, string $date, string $firstScan, string $lastScan): string
    {
        $existing = EmployeeAttendance::where('employee_id', $employeeId)->whereDate('date', $date)->first();
        if ($existing && in_array($existing->status, self::LEAVE_STATUSES, true)) {
            return 'leave_days_kept';
        }

        $in = $existing?->check_in_time ? min($this->seconds($existing->check_in_time), $firstScan) : $firstScan;
        $out = $existing?->check_out_time ? max($this->seconds($existing->check_out_time), $lastScan) : $lastScan;
        $values = [
            'check_in_time' => $in,
            // One scan in a day is an arrival with no recorded departure.
            'check_out_time' => $out === $in ? null : $out,
            'status' => $in > $this->seconds((string) config('fingerprint.late_after')) ? 'terlambat' : 'hadir',
            'source' => 'fingerprint',
        ];

        if (! $existing) {
            EmployeeAttendance::create(['employee_id' => $employeeId, 'date' => $date, ...$values]);

            return 'attendances_created';
        }

        $current = [
            'check_in_time' => $existing->check_in_time ? $this->seconds($existing->check_in_time) : null,
            'check_out_time' => $existing->check_out_time ? $this->seconds($existing->check_out_time) : null,
            'status' => $existing->status,
            'source' => $existing->source,
        ];
        if ($current === $values) {
            return 'attendances_unchanged';
        }
        $existing->update($values);

        return 'attendances_updated';
    }

    /** @return list<array<int, mixed>> */
    private function readRows(string $path, string $originalName): array
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            try {
                return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
            } catch (Throwable) {
                throw new FingerprintImportException(__('File Excel tidak bisa dibaca.'));
            }
        }

        if (! in_array($extension, ['csv', 'txt', 'dat'], true)) {
            throw new FingerprintImportException(__('Jenis file tidak dikenali. Unggah ekspor mesin dalam format CSV, TXT, DAT, atau Excel.'));
        }

        $lines = preg_split('/\r\n|\r|\n/', (string) preg_replace('/^\xEF\xBB\xBF/', '', (string) file_get_contents($path)));
        $delimiter = $this->delimiter($lines);

        return array_map(fn (string $line) => str_getcsv($line, $delimiter, '"', '\\'), $lines);
    }

    /** @param list<string> $lines */
    private function delimiter(array $lines): string
    {
        $configured = (string) config('fingerprint.delimiter');
        if ($configured !== 'auto') {
            return $configured === '\t' ? "\t" : $configured;
        }
        $first = collect($lines)->first(fn (string $line) => trim($line) !== '') ?? '';
        $counts = [',' => substr_count($first, ','), ';' => substr_count($first, ';'), "\t" => substr_count($first, "\t")];
        arsort($counts);

        return (string) array_key_first($counts);
    }

    /**
     * Column index of each field.
     *
     * @param  array<int, mixed>  $header
     * @return array{machine_id: int, datetime: ?int, date: ?int, time: ?int}
     */
    private function resolveColumns(array $header, bool $hasHeader): array
    {
        $config = config('fingerprint.columns');
        $normalised = array_map(fn ($cell) => preg_replace('/\s+/', ' ', strtolower(trim((string) $cell))), $header);
        $find = function ($spec, array $skip = []) use ($normalised, $hasHeader): ?int {
            if (is_int($spec)) {
                return $spec;
            }
            if (! $hasHeader) {
                return null;
            }
            foreach ((array) $spec as $name) {
                $index = array_search(strtolower($name), $normalised, true);
                if ($index !== false && ! in_array($index, $skip, true)) {
                    return (int) $index;
                }
            }

            return null;
        };

        $machineId = $find($config['machine_id']);
        $date = $find($config['date']);
        // A machine that splits date and time: the time is whichever time-like column is not the date.
        $time = $date !== null ? $find([...(array) $config['time'], ...(array) $config['datetime']], [$date]) : null;
        $datetime = ($date === null || $time === null) ? $find($config['datetime']) : null;

        if ($machineId === null) {
            throw new FingerprintImportException(__('Kolom ID mesin tidak ditemukan. Sesuaikan nama kolomnya di config/fingerprint.php.'));
        }
        if ($datetime === null && ($date === null || $time === null)) {
            throw new FingerprintImportException(__('Kolom tanggal/jam scan tidak ditemukan. Sesuaikan nama kolomnya di config/fingerprint.php.'));
        }

        return ['machine_id' => $machineId, 'datetime' => $datetime, 'date' => $datetime === null ? $date : null, 'time' => $datetime === null ? $time : null];
    }

    /**
     * @param  array<int, mixed>  $row
     * @param  array{machine_id: int, datetime: ?int, date: ?int, time: ?int}  $columns
     */
    private function parseMoment(array $row, array $columns): ?Carbon
    {
        if ($columns['datetime'] !== null) {
            $value = $row[$columns['datetime']] ?? null;
            if (is_numeric($value)) {
                return $this->fromExcel((float) $value);
            }
            $text = trim((string) $value);
        } else {
            $date = $row[$columns['date']] ?? null;
            $time = $row[$columns['time']] ?? null;
            if (is_numeric($date) && is_numeric($time)) {
                return $this->fromExcel((float) $date + (float) $time);
            }
            if (is_numeric($date)) {
                $date = $this->fromExcel((float) $date)?->format('Y-m-d');
            }
            $text = trim($date.' '.trim((string) $time));
        }
        if ($text === '') {
            return null;
        }

        $format = config('fingerprint.datetime_format');
        foreach ($format ? [$format] : self::FORMATS as $candidate) {
            try {
                $moment = Carbon::createFromFormat('!'.$candidate, $text);
            } catch (Throwable) {
                continue;
            }
            if ($moment !== null && $moment->format($candidate) === $text) {
                return $moment;
            }
        }

        return null;
    }

    private function fromExcel(float $serial): ?Carbon
    {
        try {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($serial))->second(0);
        } catch (Throwable) {
            return null;
        }
    }

    private function seconds(string $time): string
    {
        return strlen($time) === 5 ? $time.':00' : $time;
    }

    /** @param array<int, mixed> $row */
    private function isBlank(array $row): bool
    {
        return trim(implode('', array_map(fn ($cell) => (string) $cell, $row))) === '';
    }
}
