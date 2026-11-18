<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('report_card_templates')) {
            Schema::create('report_card_templates', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
                $table->enum('phase', ['E', 'F'])->default('F');
                $table->json('header_config')->nullable();
                $table->json('signature_config')->nullable();
                $table->json('grade_descriptors')->nullable();
                $table->json('character_dimensions')->nullable();
                $table->text('closing_remarks')->nullable();
                $table->boolean('is_published')->default(false);
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('report_card_entries')) {
            Schema::create('report_card_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_card_id')->constrained('report_card_templates')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->decimal('score', 5, 2)->default(0.00);
                $table->enum('predikat', ['A', 'B', 'C', 'D'])->default('B');
                $table->text('capaian_kompetensi')->nullable();
                $table->text('catatan_guru')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('report_card_characters')) {
            Schema::create('report_card_characters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_card_id')->constrained('report_card_templates')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('dimension');
                $table->enum('grade', ['Sangat Baik', 'Baik', 'Cukup', 'Perlu Bimbingan'])->default('Baik');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('report_card_pdfs')) {
            Schema::create('report_card_pdfs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_card_id')->constrained('report_card_templates')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('sha256_hash');
                $table->string('qr_verification_url')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_pdfs');
        Schema::dropIfExists('report_card_characters');
        Schema::dropIfExists('report_card_entries');
        Schema::dropIfExists('report_card_templates');
    }
};
