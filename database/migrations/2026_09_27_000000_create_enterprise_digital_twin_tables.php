<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_twin_simulations', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_name');
            $table->text('parameters');
            $table->json('predicted_outcomes');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_twin_simulations');
    }
};
