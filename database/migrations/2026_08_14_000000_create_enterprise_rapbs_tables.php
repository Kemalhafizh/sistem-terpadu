<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_plans', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_year', 9);
            $table->string('title');
            $table->enum('status', ['draft', 'submitted', 'approved', 'revised'])->default('draft');
            $table->decimal('total_revenue_planned', 15, 2)->default(0);
            $table->decimal('total_expense_planned', 15, 2)->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('budget_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
            $table->enum('category', ['revenue', 'expense']);
            $table->string('sub_category');
            $table->string('account_code', 50)->index();
            $table->string('description');
            $table->decimal('planned_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance_amount', 15, 2)->default(0);
            $table->enum('quarter', ['Q1', 'Q2', 'Q3', 'Q4', 'annual'])->default('annual');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_plan_id')->constrained('budget_plans')->cascadeOnDelete();
            $table->integer('revision_number');
            $table->text('reason');
            $table->foreignId('revised_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('old_total', 15, 2);
            $table->decimal('new_total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_revisions');
        Schema::dropIfExists('budget_line_items');
        Schema::dropIfExists('budget_plans');
    }
};
