<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Guru
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->date('teaching_date');
            $table->integer('jam_ke')->nullable(); // Sesi jam pelajaran (misal: 1, 2, 3)
            $table->string('topic'); // Materi yang diajarkan
            $table->text('learning_activity')->nullable(); // Aktivitas pembelajaran
            $table->string('learning_method')->nullable(); // Ceramah, Diskusi, Praktikum, dll.
            $table->text('obstacles')->nullable(); // Kendala di kelas
            $table->text('notes')->nullable(); // Catatan khusus siswa/kelas
            $table->integer('students_present')->nullable();
            $table->integer('students_absent')->nullable();
            $table->string('attachment_path')->nullable(); // File bukti opsional
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->text('reviewer_notes')->nullable(); // Catatan dari reviewer (Wakakur/Kepsek)
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Optimasi query filter rekapitulasi & monitoring
            $table->index(['user_id', 'teaching_date']);
            $table->index(['classroom_id', 'subject_id', 'teaching_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
