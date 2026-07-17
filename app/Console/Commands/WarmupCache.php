<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\Setting;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Services\AppearanceService;

class WarmupCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-cache frequently accessed settings, metadata, and configuration variables';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Cache Warmup...');

        // 1. Warmup Appearance Settings
        try {
            $appearanceService = app(AppearanceService::class);
            
            // Clear any existing cache first to force reload from DB
            Cache::forget('appearance_settings');
            Cache::forget('appearance_css_variables');

            $settings = $appearanceService->getSettings();
            
            // Re-cachecompiled CSS variables
            $cssVars = $appearanceService->generateCssVariables($settings);
            Cache::forever('appearance_css_variables', $cssVars);
            
            $this->line('✓ Appearance settings cached.');
        } catch (\Exception $e) {
            $this->error('Failed caching appearance settings: ' . $e->getMessage());
        }

        // 2. Warmup System Settings
        try {
            $settingsList = Setting::all();
            Cache::forget('settings_all');
            foreach ($settingsList as $setting) {
                Cache::forget("setting_{$setting->key}");
                Cache::forever("setting_{$setting->key}", $setting->value);
            }
            Cache::forever('settings_all', $settingsList);
            $this->line('✓ System settings cached (' . $settingsList->count() . ' records).');
        } catch (\Exception $e) {
            $this->error('Failed caching settings: ' . $e->getMessage());
        }

        // 3. Warmup Active Academic Year
        try {
            Cache::forget('academic_year_current');
            $currentYear = AcademicYear::where('is_active', true)->first();
            if ($currentYear) {
                Cache::forever('academic_year_current', $currentYear);
                $this->line("✓ Active academic year cached: {$currentYear->year} (Semester: {$currentYear->semester}).");
            } else {
                $this->warn('! No active academic year found to cache.');
            }
        } catch (\Exception $e) {
            $this->error('Failed caching academic year: ' . $e->getMessage());
        }

        // 4. Warmup Metadata Lists
        try {
            Cache::forget('classrooms_all');
            $classrooms = Classroom::allCached();
            
            Cache::forget('subjects_all');
            $subjects = Subject::allCached();
            
            $this->line('✓ Metadata cached (' . $classrooms->count() . ' classrooms, ' . $subjects->count() . ' subjects).');
        } catch (\Exception $e) {
            $this->error('Failed caching metadata: ' . $e->getMessage());
        }

        $this->info('Cache Warmup completed successfully!');
        return Command::SUCCESS;
    }
}
