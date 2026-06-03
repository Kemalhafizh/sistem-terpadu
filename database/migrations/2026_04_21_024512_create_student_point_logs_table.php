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
        Schema::create('student_point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users'); // Guru BK / Waka
            $table->integer('points'); // Positif untuk pelanggaran, Negatif untuk pengurangan/pemutihan
            $table->string('type')->default('penalty'); // penalty, reward, adjustment
            $table->text('description'); // Alasan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_point_logs');
    }
};
