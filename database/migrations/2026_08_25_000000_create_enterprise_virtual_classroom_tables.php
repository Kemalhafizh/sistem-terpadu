<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('provider', ['zoom', 'google_meet', 'jitsi'])->default('google_meet');
            $table->string('meeting_id')->nullable();
            $table->string('meeting_url');
            $table->string('meeting_password')->nullable();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->string('recording_url')->nullable();
            $table->integer('attendees_count')->default(0);
            $table->timestamps();
        });

        Schema::create('virtual_classroom_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('virtual_classroom_id')->constrained('virtual_classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_classroom_attendances');
        Schema::dropIfExists('virtual_classrooms');
    }
};
