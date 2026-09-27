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
        Schema::create('uks_medicine_restocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uks_medicine_id')->constrained('uks_medicines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->foreignId('restocked_by')->constrained('users')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uks_medicine_restocks');
    }
};
