<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 32.1 Query Performance Logs Table
        Schema::create('query_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->string('route');
            $table->text('query_sql');
            $table->float('execution_time_ms');
            $table->json('bindings')->nullable();
            $table->boolean('is_slow')->default(false);
            $table->text('stack_trace')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 32.3 Load Test Results Table
        Schema::create('load_test_results', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_name');
            $table->integer('concurrent_users');
            $table->integer('duration_seconds');
            $table->integer('total_requests');
            $table->float('requests_per_second');
            $table->float('p50_latency_ms');
            $table->float('p95_latency_ms');
            $table->float('p99_latency_ms');
            $table->float('error_rate_percent');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 32.4 System Scaling Config Table
        Schema::create('system_scaling_configs', function (Blueprint $table) {
            $table->id();
            $table->string('config_key')->unique();
            $table->text('config_value');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_scaling_configs');
        Schema::dropIfExists('load_test_results');
        Schema::dropIfExists('query_performance_logs');
    }
};
