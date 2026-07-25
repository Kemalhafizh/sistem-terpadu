<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->enum('category', ['academic', 'administrative', 'financial', 'safety', 'it'])->default('academic');
            $table->longText('content_html');
            $table->integer('version')->default(1);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->date('effective_date');
            $table->date('review_date');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'status']);
        });

        Schema::create('sop_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sop_document_id')->constrained('sop_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->unique(['sop_document_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_acknowledgements');
        Schema::dropIfExists('sop_documents');
    }
};
