<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('category')->nullable(); // 'akademik', 'keuangan', 'teknis', 'umum'
            $table->json('keywords')->nullable();   // ["nilai", "input", "grade"]
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('category');
        });

        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('user_message');
            $table->text('bot_response');
            $table->string('intent')->nullable();   // 'faq', 'navigate', 'query_data', 'unknown'
            $table->json('context_data')->nullable();
            $table->float('confidence')->nullable();
            $table->boolean('was_helpful')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('intent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
        Schema::dropIfExists('chatbot_faqs');
    }
};
