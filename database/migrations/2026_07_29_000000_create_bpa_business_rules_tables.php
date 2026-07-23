<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Business Rules Table
        Schema::create('bpa_business_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event'); // e.g. attendance.checked, employee.lateness, student.point_logged
            $table->json('conditions'); // array of [{field, operator, value}]
            $table->json('actions'); // array of [{action_type, params}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Rule Execution Logs Table
        Schema::create('bpa_rule_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bpa_business_rule_id')->constrained('bpa_business_rules')->onDelete('cascade');
            $table->string('trigger_event');
            $table->boolean('matched')->default(false);
            $table->json('actions_executed')->nullable();
            $table->string('status')->default('SUCCESS'); // SUCCESS, SKIPPED, FAILED
            $table->float('execution_time_ms')->default(0);
            $table->timestamps();

            $table->index(['trigger_event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpa_rule_executions');
        Schema::dropIfExists('bpa_business_rules');
    }
};
