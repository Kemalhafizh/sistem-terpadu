<?php

namespace App\Console\Commands;

use App\Services\Treasury\PettyCashService;
use Illuminate\Console\Command;

/**
 * One-time correction for petty-cash approvals made before PettyCashService:
 * they were marked approved but never changed the fund's balance.
 *
 * Dry run by default — it only shows what would change. `--apply` updates the
 * funds whose corrected balance stays between 0 and the imprest amount; the
 * others are listed for a treasurer to resolve first (e.g. by recording the
 * replenishment that really happened), then this can be run again.
 */
class ApplyLegacyPettyCash extends Command
{
    protected $signature = 'treasury:apply-legacy-petty-cash
        {--apply : Update fund balances (without it this is a dry run)}
        {--fund= : Only this fund id}';

    protected $description = 'Apply petty-cash approvals that never moved their fund balance (made before PettyCashService).';

    private const STATUS_LABELS = [
        'ok' => 'OK',
        'negative' => 'SALDO NEGATIF — perlu dicek',
        'over_imprest' => 'MELEBIHI PLAFON — perlu dicek',
    ];

    public function handle(PettyCashService $service): int
    {
        $fundId = $this->option('fund') !== null ? (int) $this->option('fund') : null;
        $plan = $service->legacyCorrectionPlan($fundId);

        if ($plan === []) {
            $this->info('Tidak ada transaksi kas kecil lama yang belum diterapkan ke saldo. Tidak ada yang diubah.');

            return self::SUCCESS;
        }

        $this->table(
            ['Dana', 'Saldo sekarang', 'Pengeluaran lama', 'Pengisian lama', 'Saldo terkoreksi', 'Plafon', 'Transaksi', 'Status'],
            array_map(fn (array $p) => [
                "#{$p['fund_id']} {$p['fund_name']}",
                $this->rupiah($p['current']),
                $this->rupiah($p['spending']),
                $this->rupiah($p['replenishment']),
                $this->rupiah($p['result']),
                $this->rupiah($p['imprest']),
                $p['transactions'],
                self::STATUS_LABELS[$p['status']],
            ], $plan)
        );

        if (! $this->option('apply')) {
            $this->warn('Dry run: tidak ada yang diubah. Jalankan ulang dengan --apply untuk menerapkan baris berstatus OK.');

            return self::SUCCESS;
        }

        $result = $service->applyLegacyCorrections($fundId);

        foreach ($result['applied'] as $p) {
            $this->info("Diterapkan: #{$p['fund_id']} {$p['fund_name']} → {$this->rupiah($p['result'])} ({$p['transactions']} transaksi).");
        }
        foreach ($result['skipped'] as $p) {
            $this->error("Dilewati: #{$p['fund_id']} {$p['fund_name']} — ".self::STATUS_LABELS[$p['status']].'. Saldo tidak diubah.');
        }

        return $result['skipped'] === [] ? self::SUCCESS : self::FAILURE;
    }

    private function rupiah(string $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
