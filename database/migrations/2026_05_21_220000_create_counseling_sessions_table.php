<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('counselor_id')->nullable()->constrained('users')->onDelete('set null'); // Guru BK
            $table->foreignId('requested_by')->nullable()->constrained('users')->onDelete('set null'); // Pemohon (Siswa/Ortu/BK)
            $table->dateTime('scheduled_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->enum('type', ['individual', 'group', 'home_visit', 'conference'])->default('individual');
            $table->enum('category', ['akademik', 'pribadi', 'sosial', 'karir'])->default('pribadi');
            $table->enum('status', ['requested', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('requested');

            // Kolom sensitif yang dienkripsi
            $table->text('reason');                     // Alasan / Keluhan kasus
            $table->text('session_notes')->nullable();  // Rekam jalannya diskusi BK
            $table->text('action_plan')->nullable();    // Rencana tindak lanjut siswa
            $table->text('follow_up_notes')->nullable(); // Evaluasi perkembangan siswa

            $table->date('follow_up_date')->nullable();
            $table->boolean('is_confidential')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['student_id', 'counselor_id']);
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};
