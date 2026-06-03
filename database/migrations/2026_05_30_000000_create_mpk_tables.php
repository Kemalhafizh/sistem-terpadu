<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mpk_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('osis_transaction_id')->constrained('osis_transactions')->cascadeOnDelete();
            $table->enum('status', ['verified', 'flagged'])->default('verified');
            $table->text('notes')->nullable();
            $table->foreignId('audited_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('mpk_lpjs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->enum('status', ['draft', 'submitted', 'approved', 'approved_with_notes', 'rejected'])->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_aspirations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('body');
            $table->enum('category', ['it', 'sarpras', 'akademik', 'keuangan', 'kegiatan', 'umum'])->default('umum');
            $table->enum('status', ['pending', 'reviewed', 'delegated', 'resolved'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_aspirations');
        Schema::dropIfExists('mpk_lpjs');
        Schema::dropIfExists('mpk_audits');
    }
};
