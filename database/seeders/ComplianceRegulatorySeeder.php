<?php

namespace Database\Seeders;

use App\Models\ContractRenewal;
use App\Models\DapodikExport;
use App\Models\ForensicEvent;
use App\Models\LegalContract;
use App\Models\PurgeLog;
use App\Models\RetentionPolicy;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplianceRegulatorySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin', 'name' => 'Compliance Admin']);

        // 43.1: Dapodik Export
        $exp = DapodikExport::create([
            'academic_year' => '2025/2026',
            'semester' => 'Ganjil',
            'export_file_path' => 'exports/dapodik_20260802.xml',
            'status' => 'completed',
            'records_processed' => 1120,
            'error_count' => 0,
            'exported_by' => $admin->id,
        ]);

        // 43.2: Retention Policy & Purge Log
        $pol = RetentionPolicy::create([
            'data_category' => 'Log Aktivitas Siswa Alumni > 3 Tahun',
            'retention_months' => 36,
            'action' => 'anonymize',
            'is_active' => true,
        ]);

        PurgeLog::create([
            'policy_id' => $pol->id,
            'records_affected' => 156,
            'details' => 'Masking PII NIK & No HP alumni angkatan 2022 sesuai UU PDP No. 27/2022.',
            'executed_at' => now()->subDays(2),
        ]);

        // 43.3: Forensic Event
        ForensicEvent::create([
            'user_id' => $admin->id,
            'event_type' => 'FAILED_ADMIN_LOGIN_THRESHOLD',
            'ip_address' => '182.253.14.88',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => ['attempts' => 5, 'intercepted' => true],
            'severity' => 'warning',
            'occurred_at' => now(),
        ]);

        // 43.4: Legal Contract
        $lc = LegalContract::create([
            'contract_number' => 'LEG/ISA1/2026/044',
            'title' => 'Lisensi Cloud LMS & Server Hosting Dedicated',
            'vendor_name' => 'PT Cloud Indonesia Teknologi',
            'contract_value' => 48000000,
            'start_date' => now()->subMonths(6),
            'end_date' => now()->addMonths(6),
            'status' => 'active',
            'document_path' => 'contracts/contract_044.pdf',
        ]);

        ContractRenewal::create([
            'contract_id' => $lc->id,
            'renewal_date' => now()->addMonths(5),
            'new_value' => 52000000,
            'notes' => 'Penambahan kapasitas bandwidth 1 Gbps untuk ujian nasional.',
        ]);
    }
}
