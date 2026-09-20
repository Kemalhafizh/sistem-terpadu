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
        Schema::create('warning_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('level'); // SP1, SP2, SP3
            $table->integer('point_threshold'); // 50, 75, 100
            $table->date('issued_at');
            $table->dateTime('signed_at')->nullable();
            $table->foreignId('signed_by_parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('digital_signature')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unik per student dan level
            $table->unique(['student_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warning_letters');
    }
};
