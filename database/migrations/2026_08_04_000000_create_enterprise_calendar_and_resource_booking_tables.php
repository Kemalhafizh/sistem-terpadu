<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('event_type', ['academic', 'meeting', 'ceremony', 'holiday', 'exam', 'extracurricular'])->default('academic');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->foreignId('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->enum('visibility', ['public', 'staff', 'private'])->default('public');
            $table->string('recurrence_rule')->nullable();
            $table->string('color_hex', 7)->default('#3B82F6');
            $table->timestamps();

            $table->index(['start_datetime', 'end_datetime']);
        });

        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('rsvp_status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->unique(['calendar_event_id', 'user_id']);
        });

        Schema::create('bookable_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['room', 'facility', 'equipment'])->default('room');
            $table->integer('capacity')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('resource_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookable_resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booked_by')->constrained('users')->cascadeOnDelete();
            $table->string('purpose');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['bookable_resource_id', 'start_datetime', 'end_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_bookings');
        Schema::dropIfExists('bookable_resources');
        Schema::dropIfExists('calendar_event_attendees');
        Schema::dropIfExists('calendar_events');
    }
};
