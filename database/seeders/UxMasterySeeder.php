<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UX\AccessibilityService;
use App\Services\UX\TranslationService;
use Illuminate\Database\Seeder;

class UxMasterySeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first() ?? User::factory()->create(['role' => 'superadmin']);

        // 1. Accessibility Audit Log
        $accService = app(AccessibilityService::class);
        $accService->runAudit('/dashboard', $superadmin->name);
        $accService->runAudit('/academic/competency/outcomes', $superadmin->name);
        $accService->runAudit('/hc/evaluation/cycles', $superadmin->name);

        // 2. Translation Overrides i18n
        $transService = app(TranslationService::class);
        $transService->overrideTranslation('ar', 'dashboard.title', 'مرحباً بكم في منصة المدرسة الإصلامية', $superadmin->id);
        $transService->overrideTranslation('en', 'dashboard.title', 'Welcome to Sultan Agung 1 Islamic Senior High School Platform', $superadmin->id);
        $transService->overrideTranslation('id', 'dashboard.title', 'Selamat Datang di Platform SISTA SMA Islam Sultan Agung 1 Semarang', $superadmin->id);
    }
}
