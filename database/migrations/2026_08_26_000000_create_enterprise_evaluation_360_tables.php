<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_360_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('academic_year', 9);
            $table->string('semester');
            $table->enum('status', ['draft', 'active', 'collecting', 'analyzing', 'completed'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('evaluation_360_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('evaluation_360_cycles')->cascadeOnDelete();
            $table->enum('evaluator_type', ['supervisor', 'peer', 'student', 'parent', 'self']);
            $table->json('questions')->nullable();
            $table->enum('scale_type', ['likert5', 'likert4', 'descriptive'])->default('likert5');
            $table->boolean('is_anonymous')->default(true);
            $table->timestamps();
        });

        Schema::create('evaluation_360_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('evaluation_360_instruments')->cascadeOnDelete();
            $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('scores')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('evaluation_360_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('evaluation_360_cycles')->cascadeOnDelete();
            $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
            $table->float('supervisor_avg')->nullable();
            $table->float('peer_avg')->nullable();
            $table->float('student_avg')->nullable();
            $table->float('parent_avg')->nullable();
            $table->float('self_score')->nullable();
            $table->float('overall_score')->nullable();
            $table->json('strengths')->nullable();
            $table->json('improvements')->nullable();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_360_summaries');
        Schema::dropIfExists('evaluation_360_responses');
        Schema::dropIfExists('evaluation_360_instruments');
        Schema::dropIfExists('evaluation_360_cycles');
    }
};
