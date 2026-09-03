<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pragmatic Digital Document Verification (Replaces Complex Blockchain)
        if (!Schema::hasTable('digital_verifications')) {
            Schema::create('digital_verifications', function (Blueprint $table) {
                $table->id();
                $table->string('document_number')->unique(); // e.g. IJZ-2026-SMA-001
                $table->string('document_type'); // IJAZAH, TRANSKRIP, TAHFIDZ, RAPOR, SURAT_PERINGATAN
                $table->string('student_nisn');
                $table->string('student_name');
                $table->string('issuer_title')->default('Kepala SMA Islam Sultan Agung 1');
                $table->string('issuer_name')->default('Drs. H. Sukarno, M.Pd');
                $table->string('sha256_hash');
                $table->string('qr_verification_url');
                $table->date('issued_date');
                $table->boolean('is_valid')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        // 2. Pragmatic Rules-Based At-Risk Student Alerts (Replaces ML GPU Blackbox)
        if (!Schema::hasTable('at_risk_alerts')) {
            Schema::create('at_risk_alerts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->string('risk_level'); // LOW, MEDIUM, HIGH, CRITICAL
                $table->string('trigger_reason'); // e.g. "Alpha presensi >= 3x", "Poin pelanggaran >= 50", "Rata-rata rapor < 75"
                $table->integer('unexcused_absences')->default(0);
                $table->integer('violation_points')->default(0);
                $table->decimal('current_gpa', 5, 2)->default(0.00);
                $table->string('status')->default('OPEN'); // OPEN, IN_COUNSELING, RESOLVED
                $table->text('bk_counselor_notes')->nullable();
                $table->foreignId('assigned_bk_teacher_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Single VPS Server Health Metrics (Replaces K8s Multi-Cloud Cluster)
        if (!Schema::hasTable('vps_server_health_logs')) {
            Schema::create('vps_server_health_logs', function (Blueprint $table) {
                $table->id();
                $table->string('server_hostname')->default('sista-production-vps');
                $table->decimal('cpu_usage_percent', 5, 2);
                $table->decimal('ram_usage_percent', 5, 2);
                $table->decimal('ram_used_mb', 10, 2);
                $table->decimal('ram_total_mb', 10, 2);
                $table->decimal('disk_usage_percent', 5, 2);
                $table->integer('active_mysql_connections')->default(1);
                $table->integer('redis_pending_jobs')->default(0);
                $table->decimal('response_time_ms', 8, 2)->default(25.0);
                $table->string('health_status')->default('HEALTHY'); // HEALTHY, WARNING, CRITICAL
                $table->timestamps();
            });
        }

        // 4. Pragmatic FIFO Offline Sync Queue (Replaces Vector Clock Delta Sync)
        if (!Schema::hasTable('offline_sync_queues')) {
            Schema::create('offline_sync_queues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action_type'); // ATTENDANCE_CHECKIN, MUTABAAH_LOG, TEACHING_JOURNAL
                $table->json('payload');
                $table->string('client_uuid')->unique();
                $table->string('status')->default('PROCESSED'); // PENDING, PROCESSED, FAILED
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Static Monolithic Module Catalog (Replaces WeChat-style dynamic plugin loader)
        if (!Schema::hasTable('static_module_catalogs')) {
            Schema::create('static_module_catalogs', function (Blueprint $table) {
                $table->id();
                $table->string('module_code')->unique();
                $table->string('name');
                $table->string('category');
                $table->string('route_name');
                $table->string('icon');
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_module_catalogs');
        Schema::dropIfExists('offline_sync_queues');
        Schema::dropIfExists('vps_server_health_logs');
        Schema::dropIfExists('at_risk_alerts');
        Schema::dropIfExists('digital_verifications');
    }
};
