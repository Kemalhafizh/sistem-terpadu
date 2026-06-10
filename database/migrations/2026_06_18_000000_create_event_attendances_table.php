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
        // 1. Event Attendance Events
        Schema::create('event_attendance_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'upacara', 'sholat_dhuha', 'sholat_dzuhur', 'kultum',
                'phbi', 'rapat_guru', 'osis', 'class_meeting', 'lainnya'
            ]);
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('target', ['all_students', 'all_teachers', 'specific_class', 'specific_group']);
            $table->json('target_ids')->nullable(); // classroom_ids or user_ids
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Event Attendance Records
        Schema::create('event_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('event_attendance_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa', 'terlambat'])->default('alpa');
            $table->time('check_in_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_attendance_records');
        Schema::dropIfExists('event_attendance_events');
    }
};
