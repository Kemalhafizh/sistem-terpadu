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
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('status');
            $table->decimal('distance_meters', 8, 2)->nullable()->after('location_long');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_attendances', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'distance_meters']);
        });
    }
};
