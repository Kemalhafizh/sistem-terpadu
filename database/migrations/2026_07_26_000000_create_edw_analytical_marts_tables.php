<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Analytical Daily Financials Data Mart
        Schema::create('analytical_daily_financials', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('total_spp_collected', 15, 2)->default(0.00);
            $table->decimal('total_wadiah_collected', 15, 2)->default(0.00);
            $table->decimal('total_canteen_transactions', 15, 2)->default(0.00);
            $table->decimal('cash_balance', 15, 2)->default(0.00);
            $table->unsignedInteger('transaction_count')->default(0);
            $table->timestamps();
        });

        // 2. Analytical Student KPI Data Mart
        Schema::create('analytical_student_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('date');
            $table->decimal('attendance_percentage', 5, 2)->default(100.00);
            $table->decimal('average_grade', 5, 2)->default(0.00);
            $table->unsignedInteger('mutabaah_score')->default(0);
            $table->unsignedInteger('tahfidz_juz_count')->default(0);
            $table->unsignedInteger('penalty_points')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'date']);
        });

        // 3. Analytical Employee Performance Data Mart
        Schema::create('analytical_employee_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->decimal('attendance_score', 5, 2)->default(100.00);
            $table->integer('punctuality_minutes')->default(0);
            $table->unsignedInteger('tasks_completed_count')->default(0);
            $table->unsignedInteger('cbt_exams_created')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytical_employee_performances');
        Schema::dropIfExists('analytical_student_kpis');
        Schema::dropIfExists('analytical_daily_financials');
    }
};
