<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table for Login Attempts (Audit Trail)
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();

            $table->index(['email', 'created_at']);
            $table->index(['ip_address', 'created_at']);
        });

        // 2. Table for Active Session Monitoring and Force-Logout
        Schema::create('active_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id')->unique();            // Laravel session id
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();          // 'desktop', 'mobile', 'tablet'
            $table->timestamp('last_activity_at');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_sessions');
        Schema::dropIfExists('login_attempts');
    }
};
