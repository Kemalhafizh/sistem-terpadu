<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Run: php artisan migrate:fresh --seed
     *
     * Creates ~860 demo records across 21 tables for a fully functional
     * SMA Islam Sultan Agung 1 Semarang school management demo.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // 20 users (all roles)
            AcademicSeeder::class,  // 2 academic years, 6 classrooms, 12 subjects, 36 students
            FinanceSeeder::class,   // 3 payment categories, ~220 billings, ~100 payments
            ActivitySeeder::class,  // ~180 attendances, ~288 grades, 8 point categories, ~20 point logs
            ExtrasSeeder::class,    // tahfidz, mutabaah, announcements, SPMB, employees, library
            WorkflowDefinitionSeeder::class, // Modul 5 Workflow Engine Definitions
            PortfolioBadgeSeeder::class,    // Modul 8 Portfolio Badges
            SettingSeeder::class,   // System Settings
            AccountingSeeder::class, // Accounting data
            AchievementDefinitionSeeder::class, // Gamification achievements definitions
            ChatbotFaqSeeder::class, // AI Chatbot FAQs
        ]);
    }
}
