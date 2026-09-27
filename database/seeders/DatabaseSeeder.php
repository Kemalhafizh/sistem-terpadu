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
     * Creates 3000+ demo records across SISTA modules for a fully functional,
     * highly realistic, and contextually rich school management demo
     * dedicated exclusively to SMA Islam Sultan Agung 1 Semarang.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () {
            $this->call([
                // Core Identity and Academics
                UserSeeder::class,                  // ~80 users (staff, teachers, students, parents)
                AcademicSeeder::class,              // 120 students (20 per class, 6 classrooms)
                FinanceSeeder::class,               // ~760 billings and payments
                ActivitySeeder::class,              // ~2640 daily attendances, 960 academic grades, and point logs
                ExtrasSeeder::class,                // employees, announcements, registrations, library categories/books, calendar events

                // System Configurations
                WorkflowDefinitionSeeder::class,    // Workflow definitions
                PortfolioBadgeSeeder::class,        // Portfolio badges
                SettingSeeder::class,               // Settings
                AccountingSeeder::class,            // Accounts and journals
                AchievementDefinitionSeeder::class, // Gamification achievements
                ChatbotFaqSeeder::class,            // AI FAQs

                // Advanced Modular Seeders (New)
                DailyAssessmentSeeder::class,       // 60+ daily assessments & 1200+ scores
                TahfidzDetailSeeder::class,         // ~300 ziyadah & muraja'ah setoran logs
                MutabaahDetailSeeder::class,        // ~4500 daily mutaba'ah logs for 30 days
                ForumSeeder::class,                 // 20 threads, ~100 replies, and morph reactions
                ElearningSeeder::class,             // 5 classes, 20 materials, 10 assignments, 60 submissions
                CbtSeeder::class,                   // 3 CBT exams, 90 questions, 120 attempts with violation logs
                CounselingSeeder::class,            // 10 counseling sessions with encrypted reasons/notes
                LetterSeeder::class,                // 3 letter templates, 15 official letters
                TicketSeeder::class,                // 10 support tickets with comments and assignee timelines
                SurveySeeder::class,                // 2 surveys, questions, and 55 responses
                LibraryLoanSeeder::class,           // 35 library book loans (active, returned, overdue)
                EventAttendanceSeeder::class,       // 10 events + presensi (upacara, sholat dhuha, PHBI)
                MeetingNoteSeeder::class,           // 3 meetings with attendees and action items (Modul 15.1)
                TeacherTodoSeeder::class,           // ~40+ teacher todo items with various statuses (Modul 15.3)

                // Mega Seeder v2 - Baru
                PpdbSeeder::class,
                HrdEnhancementSeeder::class,
                ExtracurricularSeeder::class,
                OsisMpkSeeder::class,
                StudentHealthSeeder::class,
                WhatsappEnhancementSeeder::class,
                AnalyticsDataSeeder::class,
                WadiahAndCanteenSeeder::class,
                CharitySeeder::class,
                SyncConfigSeeder::class,
                AssetMaintenanceTicketSeeder::class,
                LibraryBarcodeSeeder::class,
                FinancialAuditSeeder::class,
                TreasurySeeder::class,              // bank statement reconciled by the real service, petty cash via approvals
                SecurityThreatSeeder::class,
                SchoolBrandingSeeder::class,
                QueueDemoSeeder::class,
                CacheWarmupSeeder::class,
                CircuitBreakerSeeder::class,
                ApmTelemetrySeeder::class,
                ImmutableAuditLedgerSeeder::class,
                PdplPrivacySeeder::class,
                FeatureFlagSeeder::class,
                DisasterRecoverySeeder::class,
                TwoFactorSecuritySeeder::class,
                EdwOlapSeeder::class,
                VendorSlaSeeder::class,
                WebhookHubSeeder::class,
                BpaRuleEngineSeeder::class,
                NotificationCenterSeeder::class,
                InternalMessagingSeeder::class,
                DigitalSignatureSeeder::class,
                EnterpriseCalendarSeeder::class,
                DigitalAssetSeeder::class,
                OcrDocumentSeeder::class,
                ExportEngineSeeder::class,
                AccreditationSeeder::class,
                QualityAssuranceSeeder::class,
                RiskManagementSeeder::class,
                SopGovernanceSeeder::class,
                EnterpriseIntegrationSeeder::class,
                EnterprisePerformanceSeeder::class,
                EnterpriseFinancialSeeder::class,
                StudentGrowthSeeder::class,
                AcademicIntelligenceSeeder::class,
                HumanCapitalSeeder::class,
                UxMasterySeeder::class,
                DeploymentReadinessSeeder::class,
                AdvancedAnalyticsSeeder::class,
                StudentExperienceSeeder::class,
                ParentCommunitySeeder::class,
                FacilityOperationsSeeder::class,
                ComplianceRegulatorySeeder::class,
                PlatformMaturitySeeder::class,
                FamilyEngagementSeeder::class,
                GreenSchoolSeeder::class,
            ]);
        });
    }
}