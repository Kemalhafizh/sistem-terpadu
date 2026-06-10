<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['rapat_guru', 'rapat_osis', 'rapat_wali_murid', 'rapat_yayasan', 'rapat_panitia', 'lainnya']);
            $table->date('meeting_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->text('agenda')->nullable();
            $table->text('discussion')->nullable();
            $table->text('decisions')->nullable();
            $table->json('action_items')->nullable(); // JSON structure: [{"id": 1, "task": "Task name", "pic": "John Doe", "deadline": "2026-06-30", "is_completed": false}]
            $table->json('attendees')->nullable(); // Array of user IDs: [1, 2, 3] or free-text names
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_notes');
    }
};
