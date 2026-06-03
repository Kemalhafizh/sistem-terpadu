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
        Schema::table('students', function (Blueprint $table) {
            $table->index('status', 'students_status_index');
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->index('month', 'billings_month_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_status_index');
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex('billings_month_index');
        });
    }
};
