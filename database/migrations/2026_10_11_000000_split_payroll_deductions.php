<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Payroll reads attendance from EmployeeAttendanceRecap. A month without
 * attendance data has no working days, so the attendance figures are null
 * ("belum ada data"), not 0.
 *
 * Deductions are split into the attendance part, recomputed each time the
 * month is generated, and the part TU types in (kasbon, BPJS, …), which a
 * regeneration keeps. `deductions` stays the total the slip shows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('working_days')->nullable()->default(null)->change();
            $table->integer('present_days')->nullable()->default(null)->change();
            $table->integer('late_days')->nullable()->default(null)->change();
            $table->integer('absent_days')->nullable()->default(null)->change();
            $table->integer('leave_days')->nullable()->default(null)->change();
            $table->decimal('attendance_deductions', 12, 2)->default(0)->after('deductions');
            $table->decimal('other_deductions', 12, 2)->default(0)->after('attendance_deductions');
        });

        // Existing slips held one total that regeneration overwrote anyway; count it as the attendance part.
        DB::table('payrolls')->update(['attendance_deductions' => DB::raw('deductions')]);
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['attendance_deductions', 'other_deductions']);
        });
    }
};
