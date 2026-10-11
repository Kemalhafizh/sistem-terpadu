<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Fields Dapodik requires that the school had nowhere to record, so the export
 * made them up: every student was born in "Semarang" with a mother named "Ibu
 * Kandung Siswa", and the NUPTK column carried the foundation's NIY.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('birth_place')->nullable()->after('gender');
            $table->string('mother_name')->nullable()->after('address');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('nuptk', 16)->nullable()->after('employee_number');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['birth_place', 'mother_name']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('nuptk');
        });
    }
};
