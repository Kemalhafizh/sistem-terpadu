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
        // 1. Presets (Contoh: Jadwal Normal, Jadwal Ramadhan)
        Schema::create('schedule_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 2. Definisi Jam (Jam ke-1 s/d Jam ke-11)
        Schema::create('schedule_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_preset_id')->constrained()->onDelete('cascade');
            $table->integer('session_number'); // 1, 2, ..., 11
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        // 3. Mapping Jadwal
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('classroom_id')->constrained();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('teacher_id')->constrained('users');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
            $table->integer('session_start');
            $table->integer('session_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_times');
        Schema::dropIfExists('schedule_presets');
    }
};
