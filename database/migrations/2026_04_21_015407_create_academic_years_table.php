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
        Schema::create('academic_years', function (Blueprint $row) {
            $row->id();
            $row->string('year'); // Contoh: 2024/2025
            $row->enum('semester', ['Ganjil', 'Genap']);
            $row->boolean('is_active')->default(false);
            $row->date('start_date')->nullable();
            $row->date('end_date')->nullable();
            $row->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
