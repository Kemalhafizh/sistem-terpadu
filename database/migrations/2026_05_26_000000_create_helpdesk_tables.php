<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['it', 'sarpras', 'akademik', 'keuangan', 'umum'])->default('umum');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attachment_path')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('due_date')->nullable(); // SLA Deadline
            $table->softDeletes();
            $table->timestamps();

            // Indexing for high-performance retrieval and search queries
            $table->index(['status', 'priority']);
            $table->index('reporter_id');
            $table->index('assignee_id');
            $table->index('category');
            $table->index('ticket_number');
        });

        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal')->default(false); // Internal staff-only notes
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index('ticket_id');
            $table->index(['ticket_id', 'is_internal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
        Schema::dropIfExists('tickets');
    }
};
