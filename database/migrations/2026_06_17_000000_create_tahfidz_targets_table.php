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
        // 1. Create tahfidz_targets table
        Schema::create('tahfidz_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete(); // semester scope
            $table->integer('target_juz');                          // Target juz count (e.g. 5)
            $table->integer('target_pages')->default(0);            // Target pages count
            $table->integer('achieved_juz')->default(0);            // Current achieved juz count
            $table->integer('achieved_pages')->default(0);          // Current achieved pages count
            $table->enum('status', ['on_track', 'behind', 'exceeded', 'completed'])->default('on_track');
            $table->text('notes')->nullable();                      // Teacher evaluation notes
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });

        // 2. Add columns to tahfidz_logs
        Schema::table('tahfidz_logs', function (Blueprint $table) {
            $table->tinyInteger('juz')->nullable();                // Juz number (1-30) for setoran
            $table->integer('pages_count')->default(0);            // Pages count in this setoran
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahfidz_logs', function (Blueprint $table) {
            $table->dropColumn(['juz', 'pages_count']);
        });

        Schema::dropIfExists('tahfidz_targets');
    }
};
