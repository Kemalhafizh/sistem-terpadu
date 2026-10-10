<?php

use App\Http\Controllers\Accessibility\LocalizationController;
use App\Http\Controllers\Accessibility\ThemePreferenceController;
use App\Support\Access\Roles;
use Illuminate\Support\Facades\Route;

// FASE 68 display preferences and the translation manager. FASE 119 removed the
// rest of the old extended_diamond.php (voice navigation, WCAG console, the
// extra dashboards and the mobile web pages).
Route::middleware(['auth', 'verified'])->prefix('accessibility')->name('accessibility.')->group(function () {
    Route::get('translations', [LocalizationController::class, 'index'])->name('translations.index')->middleware(Roles::only('admin'));
    Route::post('translations', [LocalizationController::class, 'store'])->name('translations.store')->middleware(Roles::only('admin'));

    Route::get('theme-settings', [ThemePreferenceController::class, 'index'])->name('theme.index')->middleware(Roles::only(Roles::MEMBERS));
    Route::post('theme-settings', [ThemePreferenceController::class, 'update'])->name('theme.update')->middleware(Roles::only(Roles::MEMBERS));
});
