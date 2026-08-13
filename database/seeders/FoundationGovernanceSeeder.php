<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\FoundationUnit;
use App\Models\FoundationReport;
use App\Models\FoundationBoardMeeting;
use App\Models\WakafAsset;
use App\Models\StaffRotation;
use App\Models\FoundationKpi;
use App\Models\UnitAccreditationRecord;
use App\Models\User;

class FoundationGovernanceSeeder extends Seeder {
    public function run() {
        $user = User::first() ?? User::factory()->create();

        $tk = FoundationUnit::create(['unit_code' => 'TK-01', 'unit_name' => 'TK ISA', 'level' => 'tk', 'student_count' => 120, 'is_flagship' => false]);
        $sd = FoundationUnit::create(['unit_code' => 'SD-01', 'unit_name' => 'SD ISA', 'level' => 'sd', 'student_count' => 450, 'is_flagship' => false]);
        $smp = FoundationUnit::create(['unit_code' => 'SMP-01', 'unit_name' => 'SMP ISA 1', 'level' => 'smp', 'student_count' => 600, 'is_flagship' => false]);
        $sma = FoundationUnit::create(['unit_code' => 'SMA-01', 'unit_name' => 'SMA ISA 1', 'level' => 'sma', 'student_count' => 1200, 'is_flagship' => true]);
        $univ = FoundationUnit::create(['unit_code' => 'UNIV-01', 'unit_name' => 'Universitas UNISSULA', 'level' => 'universitas', 'student_count' => 8000, 'is_flagship' => false]);

        FoundationReport::create(['title' => 'Lap Keu', 'report_type' => 'keuangan_konsolidasi', 'period' => 'monthly', 'period_start' => now(), 'period_end' => now(), 'data_payload' => [], 'status' => 'draft', 'prepared_by' => $user->id]);
        FoundationReport::create(['title' => 'Lap Akad', 'report_type' => 'akademik_konsolidasi', 'period' => 'monthly', 'period_start' => now(), 'period_end' => now(), 'data_payload' => [], 'status' => 'draft', 'prepared_by' => $user->id]);

        FoundationBoardMeeting::create(['title' => 'Rapat', 'meeting_date' => now(), 'agenda' => 'Agenda 1', 'status' => 'completed']);

        WakafAsset::create(['asset_code' => 'WKF-01', 'asset_name' => 'Tanah 1', 'asset_type' => 'tanah', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-02', 'asset_name' => 'Tanah 2', 'asset_type' => 'tanah', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-03', 'asset_name' => 'Bangunan 1', 'asset_type' => 'bangunan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-04', 'asset_name' => 'Bangunan 2', 'asset_type' => 'bangunan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);
        WakafAsset::create(['asset_code' => 'WKF-05', 'asset_name' => 'Mobil 1', 'asset_type' => 'kendaraan', 'acquisition_value' => 100000, 'current_value' => 120000, 'acquisition_date' => now(), 'wakif_name' => 'Hamba Allah', 'utilization_status' => 'produktif']);

        StaffRotation::create(['user_id' => $user->id, 'from_unit_id' => $tk->id, 'to_unit_id' => $sd->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'active']);
        StaffRotation::create(['user_id' => $user->id, 'from_unit_id' => $sd->id, 'to_unit_id' => $smp->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'completed']);
        StaffRotation::create(['user_id' => $user->id, 'from_unit_id' => $smp->id, 'to_unit_id' => $sma->id, 'rotation_type' => 'mutasi_tetap', 'effective_date' => now(), 'reason' => 'test', 'status' => 'proposed']);

        for ($i=0; $i<10; $i++) {
            FoundationKpi::create(['kpi_name' => 'KPI '.$i, 'category' => 'akademik', 'target_value' => 100, 'actual_value' => 90, 'unit' => '%', 'fiscal_year' => 2026]);
        }

        foreach([$tk, $sd, $smp, $sma, $univ] as $unit) {
            UnitAccreditationRecord::create(['foundation_unit_id' => $unit->id, 'accreditation_body' => 'BAN', 'accreditation_grade' => 'A', 'valid_from' => now(), 'valid_until' => now()->addYears(5), 'status' => 'active']);
        }
    }
}
