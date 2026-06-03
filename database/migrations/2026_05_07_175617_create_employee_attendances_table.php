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
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();

            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit', 'cuti', 'alfa'])->default('hadir');

            // Optional Geo-tagging
            $table->string('location_lat')->nullable();
            $table->string('location_long')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // An employee can only have one attendance record per day
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
