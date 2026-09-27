<?php

namespace App\Services\Treasury;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\BankTransaction;
use App\Models\Billing;
use App\Models\DailyCashPosition;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TreasuryReconciliationService
{
    /** A payment dated up to this many days before/after a bank credit may explain it. */
    public const MATCH_WINDOW_DAYS = 3;

    /**
     * Cash taken at the counter is not credited to the bank one payment at a time,
     * so it is only matched when the bank transaction names its receipt.
     */
    private const AMOUNT_MATCH_EXCLUDED_METHODS = ['cash'];

    private const MATCHED_STATUSES = ['auto_matched', 'manual_matched'];

    private const COLUMN_ALIASES = [
        'transaction_date' => ['tanggal', 'tgl', 'date', 'transaction_date', 'tanggal_transaksi'],
        'description' => ['keterangan', 'deskripsi', 'description', 'uraian'],
        'reference_number' => ['referensi', 'reference', 'reference_number', 'no_referensi', 'ref'],
        'debit' => ['debit', 'debet', 'mutasi_debit'],
        'credit' => ['kredit', 'credit', 'mutasi_kredit'],
        'balance' => ['saldo', 'balance'],
    ];

    private const DATE_FORMATS = ['Y-m-d', 'd/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y'];

    /**
     * Import a bank statement CSV stored on the local disk. The statement period
     * comes from the transaction dates in the file, and the importer must be a
     * real user: the one given, or the one logged in.
     */
    public function importBankStatement(int $bankAccountId, string $filePath, ?int $importedBy = null): BankStatement
    {
        $importedBy ??= auth()->id();
        if (! $importedBy) {
            throw new AuthenticationException('Impor rekening koran membutuhkan pengguna yang login atau importer yang disebutkan.');
        }

        $account = BankAccount::findOrFail($bankAccountId);
        $contents = Storage::disk('local')->get($filePath);
        if ($contents === null) {
            throw new InvalidArgumentException("File rekening koran tidak ditemukan: {$filePath}.");
        }

        $rows = $this->parseStatementCsv($contents);
        if ($rows === []) {
            throw new InvalidArgumentException('File rekening koran tidak berisi transaksi.');
        }

        $dates = array_map(fn ($row) => $row['transaction_date'], $rows);
        $periodStart = min($dates);
        $periodEnd = max($dates);

        // Transactions of an overlapping statement would be counted twice in the cash position.
        $overlap = $account->statements()
            ->whereDate('period_start', '<=', $periodEnd->toDateString())
            ->whereDate('period_end', '>=', $periodStart->toDateString())
            ->first();
        if ($overlap) {
            throw new InvalidArgumentException(sprintf(
                'Periode %s s.d. %s tumpang tindih dengan rekening koran #%d (%s s.d. %s) yang sudah diimpor.',
                $periodStart->format('d/m/Y'), $periodEnd->format('d/m/Y'), $overlap->id,
                $overlap->period_start->format('d/m/Y'), $overlap->period_end->format('d/m/Y')
            ));
        }

        return DB::transaction(function () use ($account, $filePath, $importedBy, $rows, $periodStart, $periodEnd) {
            $statement = BankStatement::create([
                'bank_account_id' => $account->id,
                'statement_date' => $periodEnd,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'file_path' => $filePath,
                'total_transactions' => count($rows),
                'status' => 'imported',
                'imported_by' => $importedBy,
            ]);
            $statement->transactions()->createMany($rows);

            return $statement;
        });
    }

    /**
     * Match the statement's unmatched bank credits to real payments.
     *
     * Pass 1 uses references printed on the bank line (a payment's receipt number,
     * or a Midtrans order id "BILL-{billing_id}-…"). Pass 2 matches the rest on
     * exact amount with a payment dated within MATCH_WINDOW_DAYS; it only matches
     * when a single payment is closest, never guesses between equal candidates.
     * A payment explains at most one bank transaction. Debits are left for review.
     *
     * @return array{total: int, matched: int, unmatched: int}
     */
    public function autoReconcile(int $statementId): array
    {
        $statement = BankStatement::with('transactions')->findOrFail($statementId);

        return DB::transaction(function () use ($statement) {
            $used = BankTransaction::whereNotNull('matched_payment_id')->pluck('matched_payment_id')->flip()->all();
            $pending = $statement->transactions->where('match_status', 'unmatched');

            foreach ($pending as $tx) {
                if ($this->cents($tx->credit) <= 0 && $tx->match_notes === null) {
                    $tx->update(['match_notes' => 'Mutasi debit: tidak dicocokkan otomatis dengan pembayaran tagihan.']);
                }
            }

            $credits = $pending->filter(fn ($tx) => $this->cents($tx->credit) > 0);
            $leftover = collect();
            foreach ($credits as $tx) {
                if (! $this->matchByReference($tx, $used)) {
                    $leftover->push($tx);
                }
            }
            foreach ($leftover as $tx) {
                $this->matchByAmountAndDate($tx, $used);
            }

            return $this->refreshCounts($statement);
        });
    }

    public function manualMatch(int $transactionId, int $billingId, ?int $matchedBy = null): void
    {
        $matchedBy ??= auth()->id();
        if (! $matchedBy) {
            throw new AuthenticationException('Pencocokan manual membutuhkan pengguna yang login.');
        }

        $tx = BankTransaction::findOrFail($transactionId);
        $billing = Billing::findOrFail($billingId);
        $tx->update([
            'match_status' => 'manual_matched',
            'matched_billing_id' => $billing->id,
            'matched_payment_id' => null,
            'matched_by' => $matchedBy,
        ]);
        $this->refreshCounts($tx->statement);
    }

    public function getReconciliationSummary(int $statementId): array
    {
        $st = BankStatement::findOrFail($statementId);

        return [
            'total' => $st->total_transactions,
            'matched' => $st->matched_count,
            'unmatched' => $st->unmatched_count,
            'status' => $st->status,
        ];
    }

    public function getDiscrepancies(int $statementId): Collection
    {
        return BankTransaction::where('bank_statement_id', $statementId)
            ->whereIn('match_status', ['unmatched', 'disputed'])
            ->get();
    }

    /**
     * Daily cash position of one bank account, from its imported bank transactions:
     * inflow = credits, outflow = debits dated that day. Opening is the closing of
     * the latest earlier position plus the net flow of the days in between; with no
     * earlier position it is the account's recorded opening balance plus the net flow
     * of every earlier imported transaction. Recomputed on every call.
     */
    public function calculateTreasuryPosition(int $bankAccountId, string $date): DailyCashPosition
    {
        $account = BankAccount::findOrFail($bankAccountId);
        $day = Carbon::parse($date)->startOfDay();

        [$inflow, $outflow] = $this->flows($account->id, $day, $day);
        $opening = $this->openingBalance($account, $day);

        $position = DailyCashPosition::where('bank_account_id', $account->id)
            ->whereDate('position_date', $day->toDateString())
            ->first() ?? new DailyCashPosition(['bank_account_id' => $account->id, 'position_date' => $day]);

        $position->fill([
            'opening_balance' => $this->money($opening),
            'total_inflow' => $this->money($inflow),
            'total_outflow' => $this->money($outflow),
            'closing_balance' => $this->money($opening + $inflow - $outflow),
        ])->save();

        return $position;
    }

    /** @return array{total: int, matched: int, unmatched: int} */
    private function refreshCounts(BankStatement $statement): array
    {
        $statuses = $statement->transactions()->pluck('match_status');
        $total = $statuses->count();
        $matched = $statuses->filter(fn ($s) => in_array($s, self::MATCHED_STATUSES, true))->count();
        $unmatched = $total - $matched;

        $statement->update([
            'total_transactions' => $total,
            'matched_count' => $matched,
            'unmatched_count' => $unmatched,
            'status' => $total === 0 ? 'imported' : ($unmatched === 0 ? 'reconciled' : 'has_discrepancies'),
        ]);

        return ['total' => $total, 'matched' => $matched, 'unmatched' => $unmatched];
    }

    private function matchByReference(BankTransaction $tx, array &$used): bool
    {
        $text = trim(($tx->reference_number ?? '').' '.$tx->description);
        $amount = $this->cents($tx->credit);

        preg_match_all('/[A-Za-z0-9]+(?:-[A-Za-z0-9]+)+/', $text, $m);
        $tokens = array_values(array_unique(array_filter([...$m[0], trim((string) $tx->reference_number)])));
        if ($tokens !== []) {
            $payment = Payment::whereIn('receipt_number', $tokens)->first();
            if ($payment) {
                if (isset($used[$payment->id])) {
                    $tx->update(['match_notes' => "Referensi kuitansi {$payment->receipt_number} sudah dicocokkan dengan mutasi bank lain."]);

                    return true;
                }
                if ($this->cents($payment->amount_paid) !== $amount) {
                    $tx->update([
                        'match_status' => 'disputed',
                        'matched_billing_id' => $payment->billing_id,
                        'match_notes' => sprintf(
                            'Referensi cocok dengan kuitansi %s, tetapi nominal berbeda (bank Rp %s, kuitansi Rp %s).',
                            $payment->receipt_number, $this->display($amount), $this->display($this->cents($payment->amount_paid))
                        ),
                    ]);

                    return true;
                }
                $this->markMatched($tx, $payment, "Cocok berdasarkan nomor kuitansi {$payment->receipt_number}.", $used);

                return true;
            }
        }

        if (preg_match('/\bBILL-(\d+)-/i', $text, $bill)) {
            $candidates = Payment::where('billing_id', (int) $bill[1])->get()
                ->filter(fn ($p) => ! isset($used[$p->id]) && $this->cents($p->amount_paid) === $amount);
            $payment = $this->closestUnique($candidates, $tx->transaction_date);
            if ($payment) {
                $this->markMatched($tx, $payment, "Cocok berdasarkan order id {$bill[0]}… (tagihan #{$bill[1]}).", $used);

                return true;
            }
        }

        return false;
    }

    private function matchByAmountAndDate(BankTransaction $tx, array &$used): void
    {
        $amount = $this->cents($tx->credit);
        $date = $tx->transaction_date->copy()->startOfDay();

        $candidates = Payment::whereNotIn('payment_method', self::AMOUNT_MATCH_EXCLUDED_METHODS)
            ->whereDate('payment_date', '>=', $date->copy()->subDays(self::MATCH_WINDOW_DAYS)->toDateString())
            ->whereDate('payment_date', '<=', $date->copy()->addDays(self::MATCH_WINDOW_DAYS)->toDateString())
            ->get()
            ->filter(fn ($p) => ! isset($used[$p->id]) && $this->cents($p->amount_paid) === $amount);

        if ($candidates->isEmpty()) {
            $tx->update(['match_notes' => sprintf(
                'Tidak ada pembayaran non-tunai Rp %s dalam ±%d hari dari %s.',
                $this->display($amount), self::MATCH_WINDOW_DAYS, $date->format('d/m/Y')
            )]);

            return;
        }

        $payment = $this->closestUnique($candidates, $date);
        if (! $payment) {
            $tx->update(['match_notes' => sprintf(
                '%d pembayaran Rp %s sama dekatnya dengan tanggal mutasi; cocokkan manual.',
                $candidates->count(), $this->display($amount)
            )]);

            return;
        }

        $this->markMatched($tx, $payment, sprintf(
            'Cocok berdasarkan nominal dan tanggal (kuitansi %s, %s).',
            $payment->receipt_number, Carbon::parse($payment->payment_date)->format('d/m/Y')
        ), $used);
    }

    /** The candidate dated closest to $date, or null when there is none or a tie. */
    private function closestUnique(Collection $candidates, Carbon $date): ?Payment
    {
        if ($candidates->isEmpty()) {
            return null;
        }
        $day = $date->copy()->startOfDay();
        $byDistance = $candidates->groupBy(fn ($p) => (int) abs(Carbon::parse($p->payment_date)->startOfDay()->diffInDays($day)));
        $nearest = $byDistance->sortKeys()->first();

        return $nearest->count() === 1 ? $nearest->first() : null;
    }

    private function markMatched(BankTransaction $tx, Payment $payment, string $note, array &$used): void
    {
        $tx->update([
            'match_status' => 'auto_matched',
            'matched_billing_id' => $payment->billing_id,
            'matched_payment_id' => $payment->id,
            'match_notes' => $note,
        ]);
        $used[$payment->id] = true;
    }

    private function openingBalance(BankAccount $account, Carbon $day): int
    {
        $previous = DailyCashPosition::where('bank_account_id', $account->id)
            ->whereDate('position_date', '<', $day->toDateString())
            ->orderByDesc('position_date')
            ->first();

        if (! $previous) {
            // current_balance is the balance before the first imported transaction.
            [$inflow, $outflow] = $this->flows($account->id, null, $day->copy()->subDay());

            return $this->cents($account->current_balance) + $inflow - $outflow;
        }

        $gapStart = $previous->position_date->copy()->addDay();
        $gapEnd = $day->copy()->subDay();
        [$inflow, $outflow] = $gapStart->lte($gapEnd) ? $this->flows($account->id, $gapStart, $gapEnd) : [0, 0];

        return $this->cents($previous->closing_balance) + $inflow - $outflow;
    }

    /** @return array{0: int, 1: int} credits and debits in cents, dates inclusive; no $from means since the first one */
    private function flows(int $bankAccountId, ?Carbon $from, Carbon $to): array
    {
        $rows = BankTransaction::whereHas('statement', fn ($q) => $q->where('bank_account_id', $bankAccountId))
            ->when($from, fn ($q) => $q->whereDate('transaction_date', '>=', $from->toDateString()))
            ->whereDate('transaction_date', '<=', $to->toDateString())
            ->get(['debit', 'credit']);

        return [
            $rows->sum(fn ($r) => $this->cents($r->credit)),
            $rows->sum(fn ($r) => $this->cents($r->debit)),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function parseStatementCsv(string $contents): array
    {
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);
        $lines = preg_split('/\r\n|\n|\r/', trim($contents));
        $headerLine = array_shift($lines) ?? '';
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn ($d) => substr_count($headerLine, $d))->first();

        $columns = [];
        foreach (str_getcsv($headerLine, $delimiter, '"', '') as $index => $name) {
            $key = str_replace([' ', '-'], '_', strtolower(trim($name)));
            foreach (self::COLUMN_ALIASES as $field => $aliases) {
                if (in_array($key, $aliases, true)) {
                    $columns[$field] = $index;
                }
            }
        }
        foreach (['transaction_date', 'debit', 'credit'] as $required) {
            if (! isset($columns[$required])) {
                throw new InvalidArgumentException("Kolom '{$required}' tidak ditemukan di header CSV (contoh header: tanggal,keterangan,referensi,debit,kredit,saldo).");
            }
        }

        $rows = [];
        foreach ($lines as $i => $line) {
            $lineNo = $i + 2;
            $cells = str_getcsv($line, $delimiter, '"', '');
            if (trim(implode('', $cells)) === '') {
                continue;
            }
            $cell = fn (string $field) => isset($columns[$field]) ? trim($cells[$columns[$field]] ?? '') : '';

            $debit = $this->parseAmount($cell('debit'), $lineNo);
            $credit = $this->parseAmount($cell('credit'), $lineNo);
            if ($debit === 0 && $credit === 0) {
                throw new InvalidArgumentException("Baris {$lineNo}: debit dan kredit sama-sama kosong.");
            }

            $rows[] = [
                'transaction_date' => $this->parseDate($cell('transaction_date'), $lineNo),
                'description' => $cell('description'),
                'reference_number' => $cell('reference_number') !== '' ? $cell('reference_number') : null,
                'debit' => $this->money($debit),
                'credit' => $this->money($credit),
                'balance' => $this->money($this->parseAmount($cell('balance'), $lineNo, allowNegative: true)),
                'match_status' => 'unmatched',
            ];
        }

        return $rows;
    }

    private function parseDate(string $raw, int $lineNo): Carbon
    {
        foreach (self::DATE_FORMATS as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $raw);
            } catch (\Throwable) {
                continue;
            }
            if ($date && $date->format($format) === $raw) {
                return $date;
            }
        }
        throw new InvalidArgumentException("Baris {$lineNo}: tanggal '{$raw}' tidak dikenali (gunakan YYYY-MM-DD atau DD/MM/YYYY).");
    }

    /** Parses "1.500.000,00", "1,500,000.00", "1500000" or "Rp 1.500.000" into cents. */
    private function parseAmount(string $raw, int $lineNo, bool $allowNegative = false): int
    {
        $value = preg_replace('/[^\d,.\-]/', '', $raw);
        if ($value === '' || $value === '-') {
            return 0;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');
        if ($lastComma !== false && $lastDot !== false) {
            [$decimal, $thousands] = $lastComma > $lastDot ? [',', '.'] : ['.', ','];
            $value = str_replace([$thousands, $decimal], ['', '.'], $value);
        } elseif ($lastComma !== false) {
            $value = preg_match('/^-?\d+,\d{1,2}$/', $value) ? str_replace(',', '.', $value) : str_replace(',', '', $value);
        } elseif ($lastDot !== false && ! preg_match('/^-?\d+\.\d{1,2}$/', $value)) {
            $value = str_replace('.', '', $value);
        }

        if (! is_numeric($value) || (! $allowNegative && (float) $value < 0)) {
            throw new InvalidArgumentException("Baris {$lineNo}: nominal '{$raw}' tidak valid.");
        }

        return (int) round((float) $value * 100);
    }

    private function cents(mixed $amount): int
    {
        return (int) round((float) ($amount ?? 0) * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function display(int $cents): string
    {
        return number_format($cents / 100, 0, ',', '.');
    }
}
