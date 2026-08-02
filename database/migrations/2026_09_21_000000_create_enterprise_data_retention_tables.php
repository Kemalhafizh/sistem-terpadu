<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('data_category');
            $table->integer('retention_months')->default(36);
            $table->enum('action', ['anonymize', 'soft_delete', 'hard_delete'])->default('anonymize');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('purge_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_id')->constrained('retention_policies')->cascadeOnDelete();
            $table->integer('records_affected');
            $table->text('details')->nullable();
            $table->timestamp('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purge_logs');
        Schema::dropIfExists('retention_policies');
    }
};
