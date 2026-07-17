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
        Schema::table('grades', function (Blueprint $table) {
            $table->index('recorded_by');
        });

        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->index('recipient');
            $table->index('status');
        });

        Schema::table('whatsapp_broadcasts', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_broadcasts', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['status']);
        });

        Schema::table('whatsapp_logs', function (Blueprint $table) {
            $table->dropIndex(['recipient']);
            $table->dropIndex(['status']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['recorded_by']);
        });
    }
};
