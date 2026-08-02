<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_buses', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->integer('capacity')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bus_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('school_buses')->cascadeOnDelete();
            $table->string('route_name');
            $table->json('waypoints')->nullable();
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->timestamps();
        });

        Schema::create('bus_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('bus_routes')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('pickup_point');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bus_passengers');
        Schema::dropIfExists('bus_routes');
        Schema::dropIfExists('school_buses');
    }
};
