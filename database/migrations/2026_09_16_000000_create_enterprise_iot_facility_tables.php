<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->enum('device_type', ['power_meter', 'temperature_sensor', 'smart_lock', 'water_meter', 'cctv'])->default('power_meter');
            $table->string('location');
            $table->string('ip_address')->nullable();
            $table->enum('status', ['online', 'offline', 'warning', 'maintenance'])->default('online');
            $table->timestamps();
        });

        Schema::create('iot_sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('iot_devices')->cascadeOnDelete();
            $table->decimal('metric_value', 10, 2);
            $table->string('metric_unit');
            $table->timestamp('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_sensor_readings');
        Schema::dropIfExists('iot_devices');
    }
};
