<?php

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use App\Services\FinancialAuditorService;
use Tests\TestCase;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->staff = User::factory()->create(['role' => 'staf_tu']);
    $this->auditorService = new FinancialAuditorService();

    $this->kas = Account::firstOrCreate(['code' => '101'], [
        'name' => 'Kas Utama Sekolah',
        'type' => 'asset',
        'normal_balance' => 'debit',
    ]);

    $this->sppIncome = Account::firstOrCreate(['code' => '401'], [
        'name' => 'Pendapatan SPP Siswa',
        'type' => 'revenue',
        'normal_balance' => 'credit',
    ]);

    $this->sarprasExpense = Account::firstOrCreate(['code' => '501'], [
        'name' => 'Beban Pemeliharaan Sarpras',
        'type' => 'expense',
        'normal_balance' => 'debit',
    ]);
});

test('service can detect global balance and unbalanced journal entries', function () {
    // Balanced entry
    $balanced = JournalEntry::create([
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Penerimaan SPP Siswa Test',
        'reference' => 'REF-001',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $balanced->id,
        'account_id' => $this->kas->id,
        'debit' => 1000000,
        'credit' => 0,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $balanced->id,
        'account_id' => $this->sppIncome->id,
        'debit' => 0,
        'credit' => 1000000,
    ]);

    // Unbalanced entry (debit 500k vs credit 400k)
    $unbalanced = JournalEntry::create([
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Pengeluaran Pembelian Spidol Selisih',
        'reference' => 'REF-UNBAL-001',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $unbalanced->id,
        'account_id' => $this->sarprasExpense->id,
        'debit' => 500000,
        'credit' => 0,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $unbalanced->id,
        'account_id' => $this->kas->id,
        'debit' => 0,
        'credit' => 400000,
    ]);

    $report = $this->auditorService->runAuditCheck();

    expect($report['global_balance']['is_balanced'])->toBeFalse();
    expect($report['global_balance']['difference'])->toBe(100000.0);
    expect($report['findings_summary']['critical'])->toBeGreaterThanOrEqual(1);

    $unbalancedIds = $report['unbalanced_entries']->pluck('id')->toArray();
    expect($unbalancedIds)->toContain($unbalanced->id);
});

test('service can detect unpaired cash transactions', function () {
    $utilityExpense = Account::firstOrCreate(['code' => '502'], [
        'name' => 'Beban Listrik',
        'type' => 'expense',
        'normal_balance' => 'debit',
    ]);

    $orphanEntry = JournalEntry::create([
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Beban Listrik Tanpa Kas Berpasangan',
        'reference' => 'REF-ORPHAN-TEST',
    ]);

    JournalEntryLine::create([
        'journal_entry_id' => $orphanEntry->id,
        'account_id' => $this->sarprasExpense->id,
        'debit' => 200000,
        'credit' => 0,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $orphanEntry->id,
        'account_id' => $utilityExpense->id,
        'debit' => 0,
        'credit' => 200000,
    ]);

    $unpaired = $this->auditorService->detectOrphanOrUnpairedCashTransactions();
    $unpairedIds = $unpaired->pluck('id')->toArray();

    expect($unpairedIds)->toContain($orphanEntry->id);
});

test('admin can access financial audit dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('finance.audit.index'));
    $response->assertStatus(200);
    $response->assertSee('Auditor Jurnal Keuangan Double-Entry');
    $response->assertSee('Skor Integritas Keuangan');
});

test('staff can run live audit check via controller', function () {
    $response = $this->actingAs($this->staff)->post(route('finance.audit.run'));
    $response->assertStatus(302);
    $response->assertRedirect(route('finance.audit.index'));
});

test('admin can inspect journal details json endpoint', function () {
    $entry = JournalEntry::create([
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Detail Inspection Test',
        'reference' => 'REF-INSPECT-01',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $this->kas->id,
        'debit' => 300000,
        'credit' => 0,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $this->sppIncome->id,
        'debit' => 0,
        'credit' => 300000,
    ]);

    $response = $this->actingAs($this->admin)->get(route('finance.audit.show', $entry->id));
    $response->assertStatus(200);
    $response->assertJson([
        'id' => $entry->id,
        'description' => 'Detail Inspection Test',
        'total_debit' => 300000.0,
        'total_credit' => 300000.0,
        'is_balanced' => true,
    ]);
});

test('admin can export official audit report view', function () {
    $response = $this->actingAs($this->admin)->get(route('finance.audit.export'));
    $response->assertStatus(200);
    $response->assertSee('LAPORAN HASIL AUDIT INTEGRITAS PEMBUKUAN DOUBLE-ENTRY');
});
