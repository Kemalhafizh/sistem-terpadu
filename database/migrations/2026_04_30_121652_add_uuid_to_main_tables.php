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
        $tables = ['users', 'students', 'registrations', 'classrooms', 'academic_years'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('uuid')->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        $tables = ['users', 'students', 'registrations', 'classrooms', 'academic_years'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('uuid');
            });
        }
    }
};
