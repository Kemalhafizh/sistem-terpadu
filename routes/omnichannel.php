<?php

use App\Http\Controllers\Communication\DripCampaignController;
use App\Http\Controllers\Communication\NpsController;
use App\Http\Controllers\Communication\OmnichannelController;
use App\Http\Controllers\Communication\PublicRelationsController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// FASE 118: outbound communication, NPS and press relations: public relations and leadership.
Route::middleware(['auth', Roles::only('waka_humas', 'staf_tu', Roles::PRINCIPAL, 'admin')])->group(function () {
    Route::prefix('omnichannel')->name('omnichannel.')->group(function () {
        Route::get('/dashboard', [OmnichannelController::class, 'dashboard'])->name('dashboard');
        Route::get('/templates', [OmnichannelController::class, 'templates'])->name('templates');
        Route::get('/compose', [OmnichannelController::class, 'compose'])->name('compose');
        Route::get('/logs', [OmnichannelController::class, 'logs'])->name('logs');
    });

    Route::prefix('drip-campaigns')->name('drip_campaigns.')->group(function () {
        Route::get('/', [DripCampaignController::class, 'index'])->name('index');
        Route::get('/{id}/builder', [DripCampaignController::class, 'builder'])->name('builder');
    });

    Route::prefix('nps')->name('nps.')->group(function () {
        Route::get('/dashboard', [NpsController::class, 'dashboard'])->name('dashboard');
        Route::get('/campaigns', [NpsController::class, 'campaigns'])->name('campaigns');
        Route::get('/detractors', [NpsController::class, 'detractorFollowup'])->name('detractors');
    });

    Route::prefix('pr')->name('pr.')->group(function () {
        Route::get('/press-releases', [PublicRelationsController::class, 'pressReleases'])->name('press_releases');
        Route::get('/media-database', [PublicRelationsController::class, 'contacts'])->name('contacts');
        Route::get('/clippings', [PublicRelationsController::class, 'clippings'])->name('clippings');
    });
});
