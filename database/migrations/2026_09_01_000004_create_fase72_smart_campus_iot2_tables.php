<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('face_biometric_profiles')) {
            Schema::create('face_biometric_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->string('feature_vector_hash', 64);
                $table->json('embedding_vector'); // 512-dimensional array of floats
                $table->decimal('confidence_threshold', 4, 2)->default(0.85);
                $table->timestamp('enrolled_at');
                $table->string('status', 32)->default('active'); // active, suspended
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (!Schema::hasTable('frictionless_attendance_logs')) {
            Schema::create('frictionless_attendance_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('gate_camera_id', 64);
                $table->string('gate_location', 128);
                $table->decimal('confidence_score', 5, 2);
                $table->boolean('liveness_verified')->default(true);
                $table->timestamp('capture_timestamp');
                $table->string('status', 32)->default('verified'); // verified, flagged, rejected
                $table->timestamps();

                $table->index(['user_id', 'capture_timestamp']);
            });
        }

        if (!Schema::hasTable('room_occupancy_sensors')) {
            Schema::create('room_occupancy_sensors', function (Blueprint $table) {
                $table->id();
                $table->string('room_name', 128)->unique();
                $table->string('device_id', 64);
                $table->integer('occupancy_count')->default(0);
                $table->boolean('is_occupied')->default(false);
                $table->timestamp('last_motion_detected_at')->nullable();
                $table->string('ac_power_status', 16)->default('eco'); // on, off, eco
                $table->string('lighting_power_status', 16)->default('dim'); // on, off, dim
                $table->decimal('temperature_celsius', 4, 1)->nullable();
                $table->timestamps();

                $table->index('room_name');
            });
        }

        if (!Schema::hasTable('energy_saving_logs')) {
            Schema::create('energy_saving_logs', function (Blueprint $table) {
                $table->id();
                $table->string('room_name', 128);
                $table->string('action_taken', 255);
                $table->decimal('kwh_saved_estimate', 8, 2);
                $table->decimal('cost_saved_idr', 12, 2);
                $table->timestamp('logged_at');
                $table->timestamps();

                $table->index(['room_name', 'logged_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_saving_logs');
        Schema::dropIfExists('room_occupancy_sensors');
        Schema::dropIfExists('frictionless_attendance_logs');
        Schema::dropIfExists('face_biometric_profiles');
    }
};
