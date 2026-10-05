<?php

use App\Http\Controllers\Campus\EventTicketingController;
use App\Http\Controllers\Campus\MerchandiseController;
use App\Http\Controllers\Campus\VisitorController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// FASE 118: visitors, merchandise and event ticketing are run by the school's
// operations staff. View: operations staff and the principal; change:
// operations staff and admin. The canteen menu planner and nutrition tracker
// were removed: the canteen is run by outside vendors who rent the space,
// not by the school (the merchants' cashless POS stays, see Finance\CanteenController).
$campusChange = Roles::only('staf_tu', 'waka_sarpras', 'admin');

Route::middleware(['web', 'auth', Roles::only('staf_tu', 'waka_sarpras', Roles::PRINCIPAL, 'admin')])->prefix('campus')->group(function () use ($campusChange) {
    // Visitor Management
    Route::prefix('visitors')->name('visitors.')->group(function () use ($campusChange) {
        Route::get('/dashboard', [VisitorController::class, 'index'])->name('dashboard');
        Route::get('/kiosk', [VisitorController::class, 'kioskView'])->name('kiosk');
        Route::get('/pre-register', [VisitorController::class, 'preRegisterForm'])->name('pre_register')->middleware($campusChange);
        Route::post('/pre-register', [VisitorController::class, 'storePreRegister'])->name('store_pre_register')->middleware($campusChange);
        Route::post('/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('check_in')->middleware($campusChange);
        Route::post('/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('check_out')->middleware($campusChange);
    });

    // Merchandise Store
    Route::prefix('store')->name('store.')->group(function () use ($campusChange) {
        Route::get('/catalog', [MerchandiseController::class, 'catalog'])->name('catalog');
        Route::get('/inventory', [MerchandiseController::class, 'adminInventory'])->name('inventory');
        Route::post('/inventory', [MerchandiseController::class, 'storeItem'])->name('store_item')->middleware($campusChange);
        Route::get('/fulfillment', [MerchandiseController::class, 'fulfillment'])->name('fulfillment');
        Route::post('/checkout', [MerchandiseController::class, 'checkout'])->name('checkout')->middleware($campusChange);
    });

    // Event Ticketing
    Route::prefix('events')->name('events.')->group(function () use ($campusChange) {
        Route::get('/manager', [EventTicketingController::class, 'index'])->name('manager');
        Route::post('/manager', [EventTicketingController::class, 'store'])->name('store')->middleware($campusChange);
        Route::get('/sales-dashboard', [EventTicketingController::class, 'salesDashboard'])->name('sales_dashboard');
        Route::get('/scanner', [EventTicketingController::class, 'scannerApp'])->name('scanner_app');
        Route::post('/scan-ticket', [EventTicketingController::class, 'scanTicket'])->name('scan_ticket')->middleware($campusChange);
    });
});
