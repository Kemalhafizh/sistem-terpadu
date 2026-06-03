<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type'); // 'exam', 'holiday', 'meeting', 'ceremony', 'tryout', 'osis', 'other'
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->string('color')->default('#3b82f6');
            $table->string('target_role')->nullable(); // null = semua role
            $table->foreignId('target_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->boolean('send_notification')->default(false);
            $table->softDeletes();
            $table->timestamps();

            // Optimasi performa filter kalender
            $table->index(['start_date', 'end_date']);
            $table->index(['event_type', 'is_active']);
            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
