<?php

namespace Tests\Feature\Analytics;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Payment;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * analytics/executive/kpi: the leadership screen's figures come from the
 * attendance and billing tables, and a rate is null when nothing was
 * recorded rather than 0%.
 */
class ExecutiveKpiTest extends TestCase
{
    use RefreshDatabase;

    private const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private AcademicYear $year;

    private User $recorder;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-30 10:00:00');
        $this->year = AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
        $this->recorder = User::factory()->create(['role' => 'guru']);
        Sanctum::actingAs(User::factory()->create(['role' => 'kepala_sekolah']));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function attend(string $date, array $statuses): void
    {
        foreach ($statuses as $status) {
            $student = Student::factory()->create();
            Attendance::create([
                'student_id' => $student->id, 'academic_year_id' => $this->year->id, 'classroom_id' => $student->classroom_id,
                'date' => $date, 'status' => $status, 'recorded_by' => $this->recorder->id,
            ]);
        }
    }

    public function test_kehadiran_hari_ini_dirinci_per_status_dan_tren_hanya_hari_yang_tercatat(): void
    {
        $this->attend('2026-09-30', ['H', 'H', 'H', 'S', 'I', 'A']);
        $this->attend('2026-09-29', ['H', 'A']);
        $this->attend('2026-09-25', ['H', 'H', 'H', 'H']);
        $this->attend('2026-09-10', ['A']); // outside the 14-day window

        $response = $this->getJson('/api/v1/analytics/executive/kpi')->assertOk();

        $response->assertJsonPath('attendance.today', [
            'recorded' => 6, 'present' => 3, 'sick' => 1, 'permit' => 1, 'absent' => 1, 'rate' => 50,
        ]);
        $days = $response->json('attendance.days');
        $this->assertSame(['2026-09-25', '2026-09-29', '2026-09-30'], array_column($days, 'date'));
        $this->assertEquals([100, 50, 50], array_column($days, 'rate'));
        $this->assertSame([4, 2, 6], array_column($days, 'recorded'));
    }

    public function test_tanpa_presensi_tidak_mengarang_persentase(): void
    {
        $response = $this->getJson('/api/v1/analytics/executive/kpi')->assertOk();

        $response->assertJsonPath('attendance.today.recorded', 0)
            ->assertJsonPath('attendance.today.rate', null)
            ->assertJsonPath('attendance.days', [])
            ->assertJsonPath('spp.billed', 0)
            ->assertJsonPath('spp.settled_rate', null)
            ->assertJsonPath('spp.received_change_pct', null)
            ->assertJsonPath('spp.month', 'September 2026');
        $this->assertSame('–', $response->json('kpiList.1.value'));
    }

    public function test_pelunasan_spp_bulan_ini_dari_tagihan_tahun_ajaran_aktif(): void
    {
        $spp = PaymentCategory::create(['name' => 'SPP Bulanan', 'default_amount' => 500000, 'is_monthly' => true]);
        $oldYear = AcademicYear::create(['year' => '2025/2026', 'semester' => 'Ganjil', 'is_active' => false]);
        $bill = fn (AcademicYear $year, string $month) => Billing::factory()->create([
            'student_id' => Student::factory()->create()->id, 'payment_category_id' => $spp->id,
            'academic_year_id' => $year->id, 'month' => $month, 'amount' => 500000, 'status' => 'unpaid',
        ]);

        $paid = $bill($this->year, self::MONTHS[9]);
        $partial = $bill($this->year, '09');
        $bill($this->year, self::MONTHS[9]);
        $bill($this->year, self::MONTHS[10]);   // next month
        $bill($oldYear, self::MONTHS[9]);       // last year's September

        $pay = function (Billing $billing, int $amount, string $date) {
            Payment::factory()->create([
                'billing_id' => $billing->id, 'created_by' => $this->recorder->id,
                'amount_paid' => $amount, 'payment_date' => $date,
            ]);
            $billing->updateStatus();
        };
        $pay($paid, 500000, '2026-09-05');
        $pay($partial, 200000, '2026-09-12');
        $pay($partial, 100000, '2026-08-28');

        $response = $this->getJson('/api/v1/analytics/executive/kpi')->assertOk();

        $response->assertJsonPath('spp.bills', 3)
            ->assertJsonPath('spp.bills_paid', 1)
            ->assertJsonPath('spp.settled_rate', 53.3);
        $this->assertEquals(1500000, $response->json('spp.billed'));
        $this->assertEquals(800000, $response->json('spp.settled'));
        $this->assertEquals(700000, $response->json('spp.received_this_month'));
        $this->assertEquals(100000, $response->json('spp.received_last_month'));
        $this->assertEquals(600, $response->json('spp.received_change_pct'));
    }

    public function test_hanya_pimpinan_dan_admin(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'guru']));
        $this->getJson('/api/v1/analytics/executive/kpi')->assertForbidden();
    }
}
