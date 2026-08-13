<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Treasury\{BankAccountController, ReconciliationController, PettyCashController, TreasuryPositionController, VendorPaymentController};

Route::middleware(['auth'])->prefix('treasury')->name('treasury.')->group(function () {
    Route::resource('accounts', BankAccountController::class);
    
    Route::get('reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::get('reconciliation/import', [ReconciliationController::class, 'importForm'])->name('reconciliation.import');
    Route::post('reconciliation/import', [ReconciliationController::class, 'processImport'])->name('reconciliation.processImport');
    Route::get('reconciliation/{id}/review', [ReconciliationController::class, 'review'])->name('reconciliation.review');
    Route::post('reconciliation/manual-match', [ReconciliationController::class, 'manualMatch'])->name('reconciliation.manualMatch');

    Route::resource('petty-cash', PettyCashController::class);
    Route::post('petty-cash/{id}/approve', [PettyCashController::class, 'approve'])->name('petty-cash.approve');
    Route::post('petty-cash/{id}/reject', [PettyCashController::class, 'reject'])->name('petty-cash.reject');

    Route::get('position', [TreasuryPositionController::class, 'dashboard'])->name('position.dashboard');
    Route::get('position/trend', [TreasuryPositionController::class, 'trend'])->name('position.trend');

    Route::resource('vendor', VendorPaymentController::class);
    Route::get('vendor/calendar', [VendorPaymentController::class, 'calendar'])->name('vendor.calendar');
});