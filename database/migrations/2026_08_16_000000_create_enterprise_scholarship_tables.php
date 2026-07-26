<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['pip', 'bos', 'internal_merit', 'internal_need', 'external']);
            $table->enum('source', ['government', 'yayasan', 'corporate', 'individual']);
            $table->string('academic_year', 9);
            $table->integer('total_quota');
            $table->decimal('amount_per_student', 15, 2);
            $table->json('eligibility_criteria')->nullable();
            $table->date('application_start_date');
            $table->date('application_end_date');
            $table->enum('status', ['draft', 'open', 'closed', 'disbursed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('scholarship_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_program_id')->constrained('scholarship_programs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->json('application_data')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->decimal('gpa_score', 4, 2)->nullable();
            $table->decimal('family_income', 15, 2)->nullable();
            $table->string('recommendation_letter_path')->nullable();
            $table->enum('status', ['pending', 'shortlisted', 'approved', 'rejected', 'disbursed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarship_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_application_id')->constrained('scholarship_applications')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('disbursement_method', ['bank_transfer', 'cash', 'wadiah_credit']);
            $table->string('reference_number')->nullable();
            $table->foreignId('disbursed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('disbursed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_disbursements');
        Schema::dropIfExists('scholarship_applications');
        Schema::dropIfExists('scholarship_programs');
    }
};
