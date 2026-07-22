<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immutable_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type'); // e.g. GRADE_MUTATED, FINANCIAL_TRANSACTION, ROLE_CHANGED
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('previous_hash', 64)->default('0000000000000000000000000000000000000000000000000000000000000000');
            $table->string('current_hash', 64);
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immutable_audit_logs');
    }
};
