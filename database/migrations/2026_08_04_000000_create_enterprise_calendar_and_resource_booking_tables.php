<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};
