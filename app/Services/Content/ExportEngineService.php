<?php

namespace App\Services\Content;

use App\Exports\GenericExport;
use App\Models\ExportJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Batch export of school data from the database.
 *
 * This used to be a mock: every "export" recorded a made-up path, an estimated
 * size and a sample payload of five fields, and the download handed back a
 * four-line text file whatever the format. Now each job queries the real
 * tables, writes the file to the private `local` disk and records its actual
 * size; the download streams that file.
 */
class ExportEngineService
{
    public const ENTITIES = [
        'students' => 'Data Pokok Siswa',
        'teachers' => 'Data Guru & Tenaga Kependidikan',
        'grades' => 'Nilai Siswa',
        'finance' => 'Tagihan & Pembayaran Siswa',
        'inventory' => 'Inventaris Aset & Sarpras',
    ];

    public const FORMATS = [
        'xlsx' => ['label' => 'Excel (.xlsx)', 'ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'csv' => ['label' => 'CSV (.csv)', 'ext' => 'csv', 'mime' => 'text/csv'],
        'pdf_a' => ['label' => 'PDF (.pdf)', 'ext' => 'pdf', 'mime' => 'application/pdf'],
        'json_ld' => ['label' => 'JSON-LD (.json)', 'ext' => 'json', 'mime' => 'application/ld+json'],
        'xml_dapodik' => ['label' => 'XML (.xml)', 'ext' => 'xml', 'mime' => 'application/xml'],
    ];

    /** Who may export what. Admins see everything; others only what their job already shows them. */
    public const ENTITY_ROLES = [
        'students' => ['staf_tu', 'waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin'],
        'teachers' => ['staf_tu', 'kepala_sekolah', 'admin', 'superadmin'],
        'grades' => ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin'],
        'finance' => ['staf_tu', 'kepala_sekolah', 'admin', 'superadmin'],
        'inventory' => ['staf_tu', 'waka_sarpras', 'kepala_sekolah', 'admin', 'superadmin'],
    ];

    /** @return array<string, string> entity key => label, for this role */
    public static function entitiesFor(?string $role): array
    {
        return array_filter(
            self::ENTITIES,
            fn (string $entity) => in_array($role, self::ENTITY_ROLES[$entity], true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /** dompdf renders one big table in memory; beyond this the PDF says it is cut and points to XLSX/CSV. */
    public const PDF_MAX_ROWS = 1000;

    public const DISK = 'local';

    /**
     * Create the job and build its file straight away.
     */
    public function queueExport(string $jobName, string $entityType, string $format, int $userId): ExportJob
    {
        $exportJob = ExportJob::create([
            'job_name' => $jobName,
            'entity_type' => $entityType,
            'format' => $format,
            'status' => 'pending',
            'requested_by' => $userId,
        ]);

        return $this->processExportJob($exportJob->id);
    }

    public function processExportJob(int $exportJobId): ExportJob
    {
        $job = ExportJob::findOrFail($exportJobId);
        $job->status = 'processing';
        $job->save();

        try {
            $dataset = $this->dataset($job->entity_type);
            $path = sprintf('exports/%d_%s.%s', $job->id, Str::slug($job->job_name) ?: 'ekspor', self::FORMATS[$job->format]['ext'] ?? 'csv');

            match ($job->format) {
                'xlsx' => Excel::store($this->excelExport($dataset), $path, self::DISK, \Maatwebsite\Excel\Excel::XLSX),
                'pdf_a' => Storage::disk(self::DISK)->put($path, $this->pdf($job, $dataset)),
                'json_ld' => Storage::disk(self::DISK)->put($path, $this->jsonLd($job, $dataset)),
                'xml_dapodik' => Storage::disk(self::DISK)->put($path, $this->xml($job, $dataset)),
                default => Storage::disk(self::DISK)->put($path, $this->csv($dataset)),
            };

            $job->file_path = $path;
            $job->file_size_bytes = Storage::disk(self::DISK)->size($path);
            $job->status = 'completed';
            $job->completed_at = now();
            $job->error_message = null;
            $job->save();
        } catch (\Throwable $e) {
            report($e);
            $job->status = 'failed';
            $job->error_message = Str::limit($e->getMessage(), 500);
            $job->save();
        }

        return $job;
    }

    /**
     * The rows of one entity, straight from the database.
     *
     * Sensitive columns (addresses, salaries, bank accounts, passwords) are
     * deliberately left out: an export file travels further than the app.
     *
     * @return array{headings: list<string>, keys: list<string>, rows: list<list<string|int|float|null>>}
     */
    public function dataset(string $entityType): array
    {
        [$headings, $keys, $query] = match ($entityType) {
            'students' => [
                ['NIS', 'NISN', 'Nama', 'Kelas', 'Jenis Kelamin', 'Tanggal Lahir', 'Status'],
                ['nis', 'nisn', 'name', 'classroom', 'gender', 'birthDate', 'status'],
                DB::table('students')
                    ->join('users', 'users.id', '=', 'students.user_id')
                    ->leftJoin('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->orderBy('classrooms.name')->orderBy('users.name')
                    ->select('students.nis', 'students.nisn', 'users.name', 'classrooms.name as classroom', 'students.gender', 'students.birth_date', 'students.status'),
            ],
            'teachers' => [
                ['Nama', 'Email', 'Peran', 'NIP / No. Pegawai', 'Jabatan', 'Status Kepegawaian', 'TMT'],
                ['name', 'email', 'role', 'employeeNumber', 'position', 'employmentStatus', 'hireDate'],
                DB::table('users')
                    ->leftJoin('employees', 'employees.user_id', '=', 'users.id')
                    ->whereIn('users.role', ['guru', 'teacher', 'wali_kelas', 'bk', 'waka_kurikulum', 'waka_kesiswaan', 'waka_humas', 'waka_sarpras', 'kepala_sekolah', 'staf_tu'])
                    ->orderBy('users.name')
                    ->select('users.name', 'users.email', 'users.role', 'employees.employee_number', 'employees.position', 'employees.employment_status', 'employees.hire_date'),
            ],
            'grades' => [
                ['NIS', 'Nama', 'Kelas', 'Mata Pelajaran', 'Tahun Ajaran', 'Semester', 'Jenis', 'Nilai'],
                ['nis', 'name', 'classroom', 'subject', 'year', 'semester', 'type', 'score'],
                DB::table('grades')
                    ->join('students', 'students.id', '=', 'grades.student_id')
                    ->join('users', 'users.id', '=', 'students.user_id')
                    ->leftJoin('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->join('subjects', 'subjects.id', '=', 'grades.subject_id')
                    ->join('academic_years', 'academic_years.id', '=', 'grades.academic_year_id')
                    ->orderBy('classrooms.name')->orderBy('users.name')->orderBy('subjects.name')->orderBy('grades.type')
                    ->select('students.nis', 'users.name', 'classrooms.name as classroom', 'subjects.name as subject', 'academic_years.year', 'academic_years.semester', 'grades.type', 'grades.score'),
            ],
            'finance' => [
                ['NIS', 'Nama', 'Kelas', 'Kategori', 'Bulan', 'Nominal', 'Terbayar', 'Jatuh Tempo', 'Status'],
                ['nis', 'name', 'classroom', 'category', 'month', 'amount', 'paid', 'dueDate', 'status'],
                DB::table('billings')
                    ->join('students', 'students.id', '=', 'billings.student_id')
                    ->join('users', 'users.id', '=', 'students.user_id')
                    ->leftJoin('classrooms', 'classrooms.id', '=', 'students.classroom_id')
                    ->join('payment_categories', 'payment_categories.id', '=', 'billings.payment_category_id')
                    ->orderBy('classrooms.name')->orderBy('users.name')->orderBy('billings.due_date')
                    ->select(
                        'students.nis', 'users.name', 'classrooms.name as classroom', 'payment_categories.name as category',
                        'billings.month', 'billings.amount',
                        DB::raw('(select coalesce(sum(payments.amount_paid), 0) from payments where payments.billing_id = billings.id) as paid'),
                        'billings.due_date', 'billings.status'
                    ),
            ],
            'inventory' => [
                ['Kode Aset', 'Nama', 'Kategori', 'Ruang', 'Tanggal Beli', 'Harga Beli', 'Kondisi', 'Status'],
                ['assetCode', 'name', 'category', 'room', 'purchaseDate', 'purchasePrice', 'condition', 'status'],
                DB::table('assets')
                    ->leftJoin('asset_categories', 'asset_categories.id', '=', 'assets.category_id')
                    ->leftJoin('rooms', 'rooms.id', '=', 'assets.room_id')
                    ->orderBy('assets.asset_code')
                    ->select('assets.asset_code', 'assets.name', 'asset_categories.name as category', 'rooms.name as room', 'assets.purchase_date', 'assets.purchase_price', 'assets.condition', 'assets.status'),
            ],
            default => throw new \InvalidArgumentException("Entitas ekspor tidak dikenal: {$entityType}"),
        };

        $rows = [];
        foreach ($query->cursor() as $record) {
            $rows[] = array_map(
                fn ($value) => is_string($value) && is_numeric($value) && str_contains($value, '.') ? (float) $value : $value,
                array_values((array) $record)
            );
        }

        return ['headings' => $headings, 'keys' => $keys, 'rows' => $rows];
    }

    private function excelExport(array $dataset): GenericExport
    {
        return new GenericExport(collect($dataset['rows']), $dataset['headings'], fn (array $row) => $row);
    }

    /** RFC 4180 CSV with a UTF-8 BOM so Excel opens names with diacritics correctly. */
    private function csv(array $dataset): string
    {
        $out = fopen('php://temp', 'r+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $dataset['headings'], ',', '"', '');
        foreach ($dataset['rows'] as $row) {
            fputcsv($out, array_map(fn ($v) => $v ?? '', $row), ',', '"', '');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }

    private function pdf(ExportJob $job, array $dataset): string
    {
        $total = count($dataset['rows']);
        $data = array_slice($dataset['rows'], 0, self::PDF_MAX_ROWS);
        $note = $total > self::PDF_MAX_ROWS
            ? 'PDF ini memuat '.number_format(self::PDF_MAX_ROWS, 0, ',', '.').' dari '.number_format($total, 0, ',', '.').' baris. Gunakan format Excel atau CSV untuk data lengkap.'
            : null;

        return Pdf::loadView('import-export.pdf_export', [
            'title' => $job->job_name.' — '.(self::ENTITIES[$job->entity_type] ?? $job->entity_type),
            'headings' => $dataset['headings'],
            'data' => $data,
            'note' => $note,
        ])->setPaper('a4', 'landscape')->output();
    }

    private function jsonLd(ExportJob $job, array $dataset): string
    {
        $records = array_map(fn (array $row) => array_combine($dataset['keys'], $row), $dataset['rows']);

        return json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Dataset',
            'name' => $job->job_name,
            'description' => self::ENTITIES[$job->entity_type] ?? $job->entity_type,
            'creator' => ['@type' => 'EducationalOrganization', 'name' => school_name(), 'identifier' => school_npsn() ?: null],
            'dateCreated' => now()->toIso8601String(),
            'variableMeasured' => $dataset['headings'],
            'size' => count($records),
            'data' => $records,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Plain, well-formed XML (one element per record, one child per column).
     * It is not the official Dapodik/EMIS import schema; it is labelled as XML.
     */
    private function xml(ExportJob $job, array $dataset): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $root = $doc->createElement('export');
        $root->setAttribute('entity', $job->entity_type);
        $root->setAttribute('institution', (string) school_name());
        if (school_npsn()) {
            $root->setAttribute('npsn', (string) school_npsn());
        }
        $root->setAttribute('generated_at', now()->toIso8601String());
        $root->setAttribute('count', (string) count($dataset['rows']));
        $doc->appendChild($root);

        foreach ($dataset['rows'] as $row) {
            $record = $doc->createElement('record');
            foreach ($dataset['keys'] as $i => $key) {
                $field = $doc->createElement(Str::snake($key));
                $field->appendChild($doc->createTextNode((string) ($row[$i] ?? '')));
                $record->appendChild($field);
            }
            $root->appendChild($record);
        }

        return $doc->saveXML();
    }
}
