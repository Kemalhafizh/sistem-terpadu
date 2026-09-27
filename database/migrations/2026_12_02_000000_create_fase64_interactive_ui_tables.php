<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 64.1: Drag-and-Drop Schedule Builder Drafts
        if (! Schema::hasTable('schedule_builder_drafts')) {
            Schema::create('schedule_builder_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
                $table->string('name');
                $table->json('schedule_data');
                $table->json('conflict_reports')->nullable();
                $table->enum('status', ['draft', 'validating', 'approved', 'published'])->default('draft');
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 64.2: Interactive Seating Chart & Classroom Layout
        if (! Schema::hasTable('classroom_layouts')) {
            Schema::create('classroom_layouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->string('layout_name');
                $table->integer('grid_rows')->default(5);
                $table->integer('grid_columns')->default(6);
                $table->json('furniture_positions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('seat_assignments')) {
            Schema::create('seat_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_layout_id')->constrained('classroom_layouts')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->integer('grid_row');
                $table->integer('grid_column');
                $table->string('behavior_note')->nullable();
                $table->timestamps();
            });
        }

        // 64.3: Kanban-Style Task & Project Manager
        if (! Schema::hasTable('kanban_boards')) {
            Schema::create('kanban_boards', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->default('general'); // teaching, project, admin, school_event
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->json('columns');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('kanban_tasks')) {
            Schema::create('kanban_tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kanban_board_id')->constrained('kanban_boards')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('column_id');
                $table->integer('position_order')->default(0);
                $table->date('due_date')->nullable();
                $table->string('priority')->default('medium'); // low, medium, high, urgent
                $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 64.4: Real-Time Collaborative Whiteboard
        if (! Schema::hasTable('whiteboard_sessions')) {
            Schema::create('whiteboard_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('session_id')->unique();
                $table->string('title');
                $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
                $table->longText('canvas_state_json')->nullable();
                $table->string('snapshot_image_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whiteboard_sessions');
        Schema::dropIfExists('kanban_tasks');
        Schema::dropIfExists('kanban_boards');
        Schema::dropIfExists('seat_assignments');
        Schema::dropIfExists('classroom_layouts');
        Schema::dropIfExists('schedule_builder_drafts');
    }
};
