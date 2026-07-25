<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accreditation_standards', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SNP-1 s.d. SNP-8
            $table->string('standard_name');
            $table->string('category');
            $table->text('description')->nullable();
            $table->float('weight')->default(1.0);
            $table->timestamps();
        });

        Schema::create('accreditation_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accreditation_standard_id')->constrained('accreditation_standards')->cascadeOnDelete();
            $table->string('indicator_code');
            $table->string('indicator_name');
            $table->text('description')->nullable();
            $table->enum('data_source', ['auto', 'manual'])->default('manual');
            $table->string('auto_query')->nullable();
            $table->string('target_value');
            $table->timestamps();
        });

        Schema::create('accreditation_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accreditation_indicator_id')->constrained('accreditation_indicators')->cascadeOnDelete();
            $table->enum('evidence_type', ['document', 'data', 'photo'])->default('document');
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->string('academic_year');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditation_evidences');
        Schema::dropIfExists('accreditation_indicators');
        Schema::dropIfExists('accreditation_standards');
    }
};
