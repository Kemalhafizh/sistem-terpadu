<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('totp_secret')->nullable()->after('password');
            $table->timestamp('totp_enabled_at')->nullable()->after('totp_secret');
            $table->text('totp_recovery_codes')->nullable()->after('totp_enabled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled_at', 'totp_recovery_codes']);
        });
    }
};
