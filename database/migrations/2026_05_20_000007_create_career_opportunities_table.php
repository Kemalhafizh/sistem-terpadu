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
        Schema::create('career_opportunities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('company_name');
            $table->enum('type', ['job', 'scholarship'])->default('job');
            $table->text('description');
            $table->text('requirements');
            $table->string('contact_email');
            $table->date('closing_date');
            $table->string('file_path')->nullable(); // Unggahan PDF Brosur
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_opportunities');
    }
};
