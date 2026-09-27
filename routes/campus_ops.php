<?php

use App\Http\Controllers\Campus\EventTicketingController;
use App\Http\Controllers\Campus\MerchandiseController;
use App\Http\Controllers\Campus\NutritionController;
use App\Http\Controllers\Campus\VisitorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('campus')->group(function () {
    // Visitor Management
    Route::prefix('visitors')->name('visitors.')->group(function () {
        Route::get('/dashboard', [VisitorController::class, 'index'])->name('dashboard');
        Route::get('/kiosk', [VisitorController::class, 'kioskView'])->name('kiosk');
        Route::get('/pre-register', [VisitorController::class, 'preRegisterForm'])->name('pre_register');
        Route::post('/pre-register', [VisitorController::class, 'storePreRegister'])->name('store_pre_register');
        Route::post('/{visitor}/check-in', [VisitorController::class, 'checkIn'])->name('check_in');
        Route::post('/{visitor}/check-out', [VisitorController::class, 'checkOut'])->name('check_out');
    });

    // Merchandise Store
    Route::prefix('store')->name('store.')->group(function () {
        Route::get('/catalog', [MerchandiseController::class, 'catalog'])->name('catalog');
        Route::get('/inventory', [MerchandiseController::class, 'adminInventory'])->name('inventory');
        Route::post('/inventory', [MerchandiseController::class, 'storeItem'])->name('store_item');
        Route::get('/fulfillment', [MerchandiseController::class, 'fulfillment'])->name('fulfillment');
        Route::post('/checkout', [MerchandiseController::class, 'checkout'])->name('checkout');
    });

    // Canteen Nutrition
    Route::prefix('canteen')->name('canteen.')->group(function () {
        Route::get('/menu-planner', [NutritionController::class, 'menuPlanner'])->name('menu_planner');
        Route::post('/menu-planner', [NutritionController::class, 'storeMenu'])->name('store_menu');
        Route::get('/nutrition-tracker', [NutritionController::class, 'nutritionDashboard'])->name('nutrition_tracker');
    });

    // Event Ticketing
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/manager', [EventTicketingController::class, 'index'])->name('manager');
        Route::post('/manager', [EventTicketingController::class, 'store'])->name('store');
        Route::get('/sales-dashboard', [EventTicketingController::class, 'salesDashboard'])->name('sales_dashboard');
        Route::get('/scanner', [EventTicketingController::class, 'scannerApp'])->name('scanner_app');
        Route::post('/scan-ticket', [EventTicketingController::class, 'scanTicket'])->name('scan_ticket');
    });
});
