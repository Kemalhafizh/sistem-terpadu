<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('question_bank_categories')) {
            Schema::create('question_bank_categories', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->enum('grade_level', ['X', 'XI', 'XII'])->default('X');
                $table->string('curriculum_ref')->nullable()->comment('Code CP/TP like MAT.E.1.2');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('question_bank_items')) {
            Schema::create('question_bank_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('category_id')->constrained('question_bank_categories')->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->enum('type', ['pilihan_ganda', 'essay', 'benar_salah', 'menjodohkan', 'isian_singkat'])->default('pilihan_ganda');
                $table->longText('question_text')->comment('Supports Markdown+LaTeX');
                $table->json('options')->nullable();
                $table->text('correct_answer');
                $table->text('explanation')->nullable();
                $table->enum('difficulty', ['mudah', 'sedang', 'sulit'])->default('sedang');
                $table->enum('cognitive_level', ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])->default('C3');
                $table->json('media_attachments')->nullable();
                $table->integer('usage_count')->default(0);
                $table->decimal('discrimination_index', 5, 2)->nullable();
                $table->decimal('difficulty_index', 5, 2)->nullable();
                $table->boolean('is_validated')->default(false);
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('exam_question_links')) {
            Schema::create('exam_question_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
                $table->foreignId('question_bank_item_id')->constrained('question_bank_items')->cascadeOnDelete();
                $table->integer('order_number')->default(0);
                $table->decimal('weight', 5, 2)->default(1.00);
                $table->unique(['exam_id', 'question_bank_item_id']);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_links');
        Schema::dropIfExists('question_bank_items');
        Schema::dropIfExists('question_bank_categories');
    }
};
