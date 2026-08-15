<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carbon_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('measurement_date');
            $table->decimal('electricity_kwh', 10, 2);
            $table->decimal('water_usage_m3', 10, 2);
            $table->decimal('transport_emissions_kg', 10, 2);
            $table->decimal('paper_usage_kg', 10, 2);
            $table->decimal('total_carbon_footprint_kg', 12, 2);
            $table->json('source_data')->nullable();
            $table->text('reduction_initiatives')->nullable();
            $table->timestamps();
        });

        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->date('log_date');
            $table->string('waste_category');
            $table->decimal('weight_kg', 8, 2);
            $table->enum('disposal_method', ['recycled', 'composted', 'landfill', 'bank_sampah']);
            $table->decimal('revenue_generated', 10, 2)->default(0);
            $table->unsignedBigInteger('recorded_by');
            $table->foreign('recorded_by')->references('id')->on('employees')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('eco_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            $table->string('project_title');
            $table->text('description');
            $table->json('sdg_alignment')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->json('participating_classes')->nullable();
            $table->longText('impact_result')->nullable();
            $table->timestamps();
        });

        Schema::create('eco_participations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->string('challenge_type');
            $table->date('participation_date');
            $table->integer('points_awarded')->default(0);
            $table->text('evidence_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eco_participations');
        Schema::dropIfExists('eco_projects');
        Schema::dropIfExists('waste_logs');
        Schema::dropIfExists('carbon_metrics');
    }
};
