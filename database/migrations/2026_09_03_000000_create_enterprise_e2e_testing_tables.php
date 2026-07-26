<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e2e_test_runs', function (Blueprint $table) {
            $table->id();
            $table->string('suite_name')->default('Full Enterprise E2E Suite');
            $table->string('browser')->default('Chromium Headless');
            $table->integer('total_tests')->default(10);
            $table->integer('passed')->default(10);
            $table->integer('failed')->default(0);
            $table->integer('skipped')->default(0);
            $table->float('duration_seconds')->default(45.0);
            $table->string('status')->default('PASSED');
            $table->string('triggered_by')->default('CI/CD Pipeline');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('report_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e2e_test_runs');
    }
};
