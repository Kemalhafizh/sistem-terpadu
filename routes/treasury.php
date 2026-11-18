<?php

use App\Http\Controllers\Treasury\BankAccountController;
use App\Http\Controllers\Treasury\PettyCashController;
use App\Http\Controllers\Treasury\ReconciliationController;
use App\Http\Controllers\Treasury\TreasuryPositionController;
use App\Http\Controllers\Treasury\VendorPaymentController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// treasury was open to every signed-in account. View: finance staff
// and the principal. Change: finance staff (staf_tu) and admin; petty-cash
// approval also by the principal (the requester can never approve their own).
$treasuryChange = Roles::only('staf_tu', 'admin');

// Static paths (calendar, report, approvals, discrepancies) are registered before the
// resource routes, otherwise "{vendor}" / "{petty_cash}" would capture them.
Route::middleware(['auth', Roles::only('staf_tu', Roles::PRINCIPAL, 'admin')])->prefix('treasury')->name('treasury.')->group(function () use ($treasuryChange) {
    Route::resource('accounts', BankAccountController::class)->except(['index', 'show'])->middleware($treasuryChange);
    Route::resource('accounts', BankAccountController::class)->only(['index', 'show']);

    Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::get('reconciliation/import', [ReconciliationController::class, 'importForm'])->name('reconciliation.import')->middleware($treasuryChange);
    Route::post('reconciliation/import', [ReconciliationController::class, 'processImport'])->name('reconciliation.processImport')->middleware($treasuryChange);
    Route::get('reconciliation/discrepancies', [ReconciliationController::class, 'discrepancies'])->name('reconciliation.discrepancies');
    Route::get('reconciliation/{id}/review', [ReconciliationController::class, 'review'])->name('reconciliation.review');
    Route::post('reconciliation/{id}/auto-reconcile', [ReconciliationController::class, 'autoReconcile'])->name('reconciliation.autoReconcile')->middleware($treasuryChange);
    Route::post('reconciliation/manual-match', [ReconciliationController::class, 'manualMatch'])->name('reconciliation.manualMatch')->middleware($treasuryChange);

    Route::get('petty-cash/approvals', [PettyCashController::class, 'approvals'])->name('petty-cash.approvals');
    Route::get('petty-cash/report', [PettyCashController::class, 'report'])->name('petty-cash.report');
    Route::get('petty-cash/funds/create', [PettyCashController::class, 'createFund'])->name('petty-cash.funds.create')->middleware($treasuryChange);
    Route::post('petty-cash/funds', [PettyCashController::class, 'storeFund'])->name('petty-cash.funds.store')->middleware($treasuryChange);
    Route::resource('petty-cash', PettyCashController::class)->only(['create', 'store'])->middleware($treasuryChange);
    Route::resource('petty-cash', PettyCashController::class)->only(['index', 'show']);
    Route::post('petty-cash/{id}/approve', [PettyCashController::class, 'approve'])->name('petty-cash.approve');
    Route::post('petty-cash/{id}/reject', [PettyCashController::class, 'reject'])->name('petty-cash.reject');

    Route::get('position', [TreasuryPositionController::class, 'dashboard'])->name('position.dashboard');
    Route::get('position/trend', [TreasuryPositionController::class, 'trend'])->name('position.trend');

    Route::get('vendor/calendar', [VendorPaymentController::class, 'calendar'])->name('vendor.calendar');
    Route::resource('vendor', VendorPaymentController::class)->only(['create', 'store'])->middleware($treasuryChange);
    Route::resource('vendor', VendorPaymentController::class)->only(['index', 'show']);
    Route::post('vendor/{vendor}/pay', [VendorPaymentController::class, 'markPaid'])->name('vendor.pay')->middleware($treasuryChange);
    Route::post('vendor/{vendor}/cancel', [VendorPaymentController::class, 'cancel'])->name('vendor.cancel')->middleware($treasuryChange);
    Route::get('vendor/{vendor}/proof', [VendorPaymentController::class, 'proof'])->name('vendor.proof');
});
