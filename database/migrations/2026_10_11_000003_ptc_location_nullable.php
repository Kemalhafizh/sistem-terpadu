<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * A conference session without a stated place stays without one; the service
 * used to fill in "Ruang Kelas / Zoom" because the column was required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ptc_sessions', function (Blueprint $table) {
            $table->string('location')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ptc_sessions', function (Blueprint $table) {
            $table->string('location')->nullable(false)->change();
        });
    }
};
