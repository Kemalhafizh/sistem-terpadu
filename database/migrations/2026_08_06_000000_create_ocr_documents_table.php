<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocr_documents', function (Blueprint $table) {
            $table->id();
            $table->string('original_file_path');
            $table->enum('document_type', ['ktp', 'kk', 'akta', 'ijazah', 'other'])->default('ktp');
            $table->json('extracted_data')->nullable();
            $table->float('confidence_score')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocr_documents');
    }
};
