<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cctv_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('camera_id');
            $table->string('zone_name');
            $table->dateTime('incident_time');
            $table->enum('incident_type', ['unauthorized_access', 'crowd_anomaly', 'after_hours_movement', 'manual_flag']);
            $table->string('snapshot_url')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'false_alarm'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('trigger_source');
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location_description');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('alert_level', ['low', 'medium', 'critical']);
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->text('post_incident_report')->nullable();
            $table->timestamps();
        });

        Schema::create('safety_drills', function (Blueprint $table) {
            $table->id();
            $table->string('drill_type');
            $table->date('scheduled_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('evacuation_time_seconds')->nullable();
            $table->integer('headcount_unaccounted')->default(0);
            $table->text('observer_notes')->nullable();
            $table->enum('compliance_status', ['scheduled', 'completed', 'failed', 'rescheduled'])->default('scheduled');
            $table->timestamps();
        });

        Schema::create('zone_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('zone_name');
            $table->enum('breach_type', ['unauthorized_exit', 'restricted_area_entry']);
            $table->dateTime('breach_time');
            $table->string('detected_by');
            $table->boolean('is_acknowledged')->default(false);
            $table->text('security_action_taken')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_breaches');
        Schema::dropIfExists('safety_drills');
        Schema::dropIfExists('emergency_alerts');
        Schema::dropIfExists('cctv_incidents');
    }
};