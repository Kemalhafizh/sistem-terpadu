<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('student_risk_profiles')) {
            Schema::create('student_risk_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
                $table->decimal('academic_risk_score', 5, 2); // 0.00 - 100.00
                $table->decimal('attendance_risk_score', 5, 2);
                $table->decimal('behavioral_risk_score', 5, 2);
                $table->string('composite_risk_level', 32)->default('low'); // low, medium, high, critical
                $table->decimal('predicted_dropout_probability', 5, 2); // percentage 0.00 - 100.00
                $table->json('risk_factors');
                $table->json('intervention_recommendations');
                $table->timestamp('assessed_at');
                $table->timestamps();

                $table->index(['composite_risk_level', 'predicted_dropout_probability']);
            });
        }

        if (!Schema::hasTable('treasury_forecasts')) {
            Schema::create('treasury_forecasts', function (Blueprint $table) {
                $table->id();
                $table->string('forecast_month', 16); // YYYY-MM
                $table->decimal('projected_tuition_inflow', 15, 2);
                $table->decimal('projected_operational_outflow', 15, 2);
                $table->decimal('projected_net_cash', 15, 2);
                $table->decimal('confidence_lower', 15, 2);
                $table->decimal('confidence_upper', 15, 2);
                $table->decimal('historical_trend_factor', 5, 2)->default(1.00);
                $table->string('forecast_scenario', 32)->default('base'); // conservative, base, optimistic
                $table->timestamps();

                $table->index(['forecast_month', 'forecast_scenario']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_forecasts');
        Schema::dropIfExists('student_risk_profiles');
    }
};
