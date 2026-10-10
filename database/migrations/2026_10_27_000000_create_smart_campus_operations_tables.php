<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_name');
            $table->string('identity_number')->nullable();
            $table->string('organization_name')->nullable();
            $table->foreignId('host_employee_id')->nullable(); // Assuming employees table exists
            $table->enum('purpose', ['meeting', 'maintenance', 'delivery', 'parent_visit', 'other']);
            $table->dateTime('check_in_time')->nullable();
            $table->dateTime('check_out_time')->nullable();
            $table->string('badge_number')->unique();
            $table->enum('status', ['pre_registered', 'active', 'completed', 'denied'])->default('pre_registered');
            $table->timestamps();
        });

        Schema::create('ticketed_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->text('description');
            $table->dateTime('event_datetime');
            $table->string('venue');
            $table->integer('max_capacity');
            $table->boolean('is_public')->default(false);
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticketed_event_id')->constrained('ticketed_events')->onDelete('cascade');
            $table->string('ticket_code')->unique();
            $table->string('attendee_name');
            $table->string('attendee_email')->nullable();
            $table->enum('ticket_type', ['vip', 'regular', 'student']);
            $table->decimal('price', 10, 2);
            $table->boolean('is_scanned')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tickets');
        Schema::dropIfExists('ticketed_events');
        Schema::dropIfExists('visitor_logs');
    }
};
