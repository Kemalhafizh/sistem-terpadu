<?php

use App\Models\Account;
use App\Models\BudgetPlan;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\Services\Financial\BudgetPlanningService;
use Illuminate\Support\Facades\Route;

/*
 * RKAS / RAPBS (119.9). Its routes carried no role, so any signed-in account
 * (a student included) could draft, submit and approve the school budget, and
 * the drafter could approve their own plan. Realisation was typed once when a
 * line was added and never updated, "0%" meant "no data", and the forecast
 * page assumed 5% growth into a hardcoded "2027/2028".
 */

beforeEach(function () {
    $this->tu = User::factory()->create(['role' => 'staf_tu']);
    $this->principal = User::factory()->create(['role' => 'kepala_sekolah']);
    $this->service = app(BudgetPlanningService::class);
});

function rkasPlan(object $test, string $year = '2026/2027'): BudgetPlan
{
    $plan = $test->service->createPlan(['fiscal_year' => $year, 'title' => 'RKAS '.$year], $test->tu->id);
    $test->service->addLineItem($plan, ['category' => 'revenue', 'sub_category' => 'SPP', 'account_code' => '4010', 'description' => 'SPP siswa', 'planned_amount' => 1000000, 'quarter' => 'annual']);
    $test->service->addLineItem($plan, ['category' => 'expense', 'sub_category' => 'ATK', 'account_code' => 'ATK-01', 'description' => 'Alat tulis', 'planned_amount' => 400000, 'quarter' => 'annual']);

    return $plan->fresh();
}

function ledger(string $code, string $type, string $date, float $debit, float $credit): void
{
    $account = Account::firstOrCreate(['code' => $code], ['name' => $code, 'type' => $type, 'normal_balance' => $type === 'revenue' ? 'credit' : 'debit']);
    $entry = JournalEntry::create(['entry_date' => $date, 'description' => 'Pembayaran']);
    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $account->id, 'debit' => $debit, 'credit' => $credit]);
}

test('alur TU menyusun, mengajukan, kepala sekolah menyetujui', function () {
    $this->actingAs($this->tu)->post(route('financial.budget.store'), ['fiscal_year' => '2026/2027', 'title' => 'RKAS 2026/2027'])->assertRedirect();
    $plan = BudgetPlan::sole();

    // Nothing to submit yet.
    $this->actingAs($this->tu)->post(route('financial.budget.submit', $plan->id))->assertSessionHas('error');

    $this->actingAs($this->tu)->post(route('financial.budget.store_line_item', $plan->id), [
        'category' => 'expense', 'sub_category' => 'ATK', 'account_code' => 'ATK-01', 'description' => 'Alat tulis', 'planned_amount' => 400000, 'quarter' => 'annual',
    ])->assertSessionHas('success');
    expect((float) $plan->fresh()->total_expense_planned)->toBe(400000.0);

    $this->actingAs($this->tu)->post(route('financial.budget.submit', $plan->id))->assertSessionHas('success');
    // TU cannot approve; the principal does.
    $this->actingAs($this->tu)->post(route('financial.budget.approve', $plan->id))->assertForbidden();
    $this->actingAs($this->principal)->post(route('financial.budget.approve', $plan->id))->assertSessionHas('success');

    expect($plan->fresh())->status->toBe('approved')->approved_by->toBe($this->principal->id);

    // Approved: no more line items.
    $this->actingAs($this->tu)->post(route('financial.budget.store_line_item', $plan->id), [
        'category' => 'expense', 'sub_category' => 'X', 'account_code' => 'X', 'description' => 'X', 'planned_amount' => 1, 'quarter' => 'annual',
    ])->assertSessionHas('error');
    expect($plan->lineItems()->count())->toBe(1);
});

test('penyusun tidak bisa menyetujui RKAS buatannya sendiri, dan draf tidak bisa langsung disetujui', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $plan = $this->service->createPlan(['fiscal_year' => '2026', 'title' => 'RKAS'], $admin->id);
    $this->service->addLineItem($plan, ['category' => 'expense', 'sub_category' => 'ATK', 'account_code' => 'A', 'description' => 'A', 'planned_amount' => 1, 'quarter' => 'annual']);

    $this->actingAs($this->principal)->post(route('financial.budget.approve', $plan->id))->assertSessionHas('error');
    expect($plan->fresh()->status)->toBe('draft');

    $this->service->submitForApproval($plan);
    $this->actingAs($admin)->post(route('financial.budget.approve', $plan->id))->assertSessionHas('error', 'RKAS tidak dapat disetujui oleh penyusunnya sendiri.');
    expect($plan->fresh()->status)->toBe('submitted');
});

test('siswa, guru, dan orang tua tidak bisa membuka atau mengubah RKAS', function (string $role) {
    $plan = rkasPlan($this);
    $this->service->submitForApproval($plan);
    $this->actingAs(User::factory()->create(['role' => $role]));

    $this->get(route('financial.budget.dashboard'))->assertForbidden();
    $this->get(route('financial.budget.show', $plan->id))->assertForbidden();
    $this->post(route('financial.budget.store'), ['fiscal_year' => '2026', 'title' => 'x'])->assertForbidden();
    $this->post(route('financial.budget.approve', $plan->id))->assertForbidden();

    expect($plan->fresh()->status)->toBe('submitted');
})->with(['siswa', 'student', 'guru', 'orang_tua']);

test('realisasi dari buku besar dalam tahun anggaran, pos tanpa akun dicatat manual', function () {
    $plan = rkasPlan($this);
    ledger('4010', 'revenue', '2026-08-15', 0, 600000);
    ledger('4010', 'revenue', '2026-05-01', 0, 999999); // before the 2026/2027 school year

    $report = $this->service->getVarianceReport($plan);

    // Revenue from the ledger; the expense line has no account and nothing recorded: unknown, not 0.
    expect($report['revenue'])->toMatchArray(['planned' => 1000000.0, 'actual' => 600000.0, 'rate' => 60.0])
        ->and($report['expense'])->toMatchArray(['planned' => 400000.0, 'actual' => null, 'rate' => null])
        ->and($report['net_surplus_deficit'])->toBeNull();

    $expenseLine = $plan->lineItems()->where('category', 'expense')->sole();
    $this->actingAs($this->tu)->put(route('financial.budget.realization', [$plan->id, $expenseLine->id]), ['actual_amount' => 100000])->assertSessionHas('success');

    $report = $this->service->getVarianceReport($plan);
    expect($report['expense'])->toMatchArray(['actual' => 100000.0, 'variance' => 300000.0, 'rate' => 25.0])
        ->and($report['net_surplus_deficit'])->toBe(500000.0);

    // A ledger-backed line cannot be typed over.
    $revenueLine = $plan->lineItems()->where('category', 'revenue')->sole();
    $this->actingAs($this->tu)->put(route('financial.budget.realization', [$plan->id, $revenueLine->id]), ['actual_amount' => 1])->assertSessionHas('error');
});

test('halaman RKAS tanpa proyeksi karangan dan tanpa 0% palsu', function () {
    $plan = rkasPlan($this);

    $this->actingAs($this->principal)->get(route('financial.budget.variance', $plan->id))
        ->assertOk()
        ->assertSee('– Terserap')
        ->assertDontSee('0% Terserap');

    $this->actingAs($this->tu)->get(route('financial.budget.dashboard'))->assertOk()->assertDontSee('Proyeksi')->assertDontSee('2027/2028');
    $this->actingAs($this->tu)->get(route('financial.budget.show', $plan->id))->assertOk()->assertSee('Ajukan ke Kepala Sekolah');
    expect(Route::has('financial.budget.forecast'))->toBeFalse();
});

test('RKAS ada di menu Keuangan untuk TU dan kepala sekolah', function () {
    foreach ([$this->tu, $this->principal] as $user) {
        $this->actingAs($user)->get(route('dashboard'))->assertSee(route('financial.budget.dashboard'), false);
    }
});
