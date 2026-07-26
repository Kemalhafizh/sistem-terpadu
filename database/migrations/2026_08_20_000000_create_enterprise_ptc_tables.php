<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ptc_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('academic_year', 9);
            $table->enum('semester', ['odd', 'even'])->default('odd');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('slot_duration_minutes')->default(15);
            $table->string('location')->default('Ruang Kelas / Online Zoom');
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->integer('max_slots_per_teacher')->default(10);
            $table->timestamps();
        });

        Schema::create('ptc_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ptc_session_id')->constrained('ptc_sessions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('topic')->nullable();
            $table->enum('status', ['available', 'booked', 'completed', 'cancelled', 'no_show'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ptc_slots');
        Schema::dropIfExists('ptc_sessions');
    }
};
