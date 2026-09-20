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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('title');
            $table->string('field'); // "Matematika & Sains", "Keagamaan & Tahfidz", "Olahraga", "Seni Budaya"
            $table->enum('level', ['KOTA', 'PROVINSI', 'NASIONAL', 'INTERNASIONAL'])->default('KOTA');
            $table->string('organizer');
            $table->date('event_date');
            $table->enum('verification_status', ['PENDING_REVIEW', 'VALIDATED', 'REJECTED'])->default('PENDING_REVIEW');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('certificate_path')->nullable();
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'verification_status']);
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
