<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('self_evaluation_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->enum('semester', ['ganjil', 'genap'])->default('genap');
            $table->enum('status', ['draft', 'active', 'completed'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('self_evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('self_evaluation_cycles')->cascadeOnDelete();
            $table->foreignId('standard_id')->constrained('accreditation_standards')->cascadeOnDelete();
            $table->text('question_text');
            $table->text('response_text')->nullable();
            $table->integer('score'); // 1-4 scale
            $table->text('evidence_notes')->nullable();
            $table->foreignId('respondent_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['cycle_id', 'standard_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('self_evaluation_responses');
        Schema::dropIfExists('self_evaluation_cycles');
    }
};
