<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_registers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['Academic', 'Financial', 'Safety', 'IT/Cyber', 'Reputational', 'Regulatory'])->default('Safety');
            $table->enum('likelihood', ['rare', 'unlikely', 'possible', 'likely', 'almost_certain'])->default('possible');
            $table->enum('impact', ['insignificant', 'minor', 'moderate', 'major', 'catastrophic'])->default('moderate');
            $table->text('mitigation_plan')->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['identified', 'mitigating', 'resolved', 'accepted'])->default('identified');
            $table->date('review_date')->nullable();
            $table->timestamps();

            $table->index(['category', 'status']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('category')->default('Safety');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable();
            $table->enum('status', ['reported', 'investigating', 'resolved', 'closed'])->default('reported');
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['severity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('risk_registers');
    }
};
