<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('event_type', ['prospective', 'applied', 'accepted', 'enrolled', 'promoted', 'retained', 'transferred_in', 'transferred_out', 'suspended', 'graduated', 'dropped_out']);
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('academic_year', 9);
            $table->enum('semester', ['odd', 'even'])->default('odd');
            $table->foreignId('triggered_by')->constrained('users')->cascadeOnDelete();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('student_lifecycle_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('milestone_type', ['first_day', 'first_exam', 'first_achievement', 'tahfidz_30juz', 'graduated', 'alumni_registered']);
            $table->timestamp('achieved_at')->useCurrent();
            $table->string('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_lifecycle_milestones');
        Schema::dropIfExists('student_lifecycle_events');
    }
};
