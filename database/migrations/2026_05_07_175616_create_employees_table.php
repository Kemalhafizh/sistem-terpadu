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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // NIY (Nomor Induk Yayasan) instead of NIP, as it's a private school
            $table->string('employee_number')->unique()->nullable();

            $table->string('position'); // e.g., Guru Fisika, Wali Kelas X, Staf TU

            // GTY (Guru Tetap Yayasan), GTT (Guru Tidak Tetap), Staf Tetap, Honorer
            $table->enum('employment_status', ['gty', 'gtt', 'staf_tetap', 'honorer'])->default('gtt');

            $table->date('hire_date')->nullable();

            // Payroll info
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('hourly_rate', 15, 2)->default(0); // For honorarium calculation

            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
