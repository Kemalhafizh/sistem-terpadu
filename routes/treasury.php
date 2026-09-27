<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Treasury\{BankAccountController, ReconciliationController, PettyCashController, TreasuryPositionController, VendorPaymentController};

// Static paths (calendar, report, approvals, discrepancies) are registered before the
// resource routes, otherwise "{vendor}" / "{petty_cash}" would capture them.
Route::middleware(['auth'])->prefix('treasury')->name('treasury.')->group(function () {
    Route::resource('accounts', BankAccountController::class);

    Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::get('reconciliation/import', [ReconciliationController::class, 'importForm'])->name('reconciliation.import');
    Route::post('reconciliation/import', [ReconciliationController::class, 'processImport'])->name('reconciliation.processImport');
    Route::get('reconciliation/discrepancies', [ReconciliationController::class, 'discrepancies'])->name('reconciliation.discrepancies');
    Route::get('reconciliation/{id}/review', [ReconciliationController::class, 'review'])->name('reconciliation.review');
    Route::post('reconciliation/{id}/auto-reconcile', [ReconciliationController::class, 'autoReconcile'])->name('reconciliation.autoReconcile');
    Route::post('reconciliation/manual-match', [ReconciliationController::class, 'manualMatch'])->name('reconciliation.manualMatch');

    Route::get('petty-cash/approvals', [PettyCashController::class, 'approvals'])->name('petty-cash.approvals');
    Route::get('petty-cash/report', [PettyCashController::class, 'report'])->name('petty-cash.report');
    Route::get('petty-cash/funds/create', [PettyCashController::class, 'createFund'])->name('petty-cash.funds.create');
    Route::post('petty-cash/funds', [PettyCashController::class, 'storeFund'])->name('petty-cash.funds.store');
    Route::resource('petty-cash', PettyCashController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('petty-cash/{id}/approve', [PettyCashController::class, 'approve'])->name('petty-cash.approve');
    Route::post('petty-cash/{id}/reject', [PettyCashController::class, 'reject'])->name('petty-cash.reject');

    Route::get('position', [TreasuryPositionController::class, 'dashboard'])->name('position.dashboard');
    Route::get('position/trend', [TreasuryPositionController::class, 'trend'])->name('position.trend');

    Route::get('vendor/calendar', [VendorPaymentController::class, 'calendar'])->name('vendor.calendar');
    Route::resource('vendor', VendorPaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('vendor/{vendor}/pay', [VendorPaymentController::class, 'markPaid'])->name('vendor.pay');
    Route::post('vendor/{vendor}/cancel', [VendorPaymentController::class, 'cancel'])->name('vendor.cancel');
    Route::get('vendor/{vendor}/proof', [VendorPaymentController::class, 'proof'])->name('vendor.proof');
});
