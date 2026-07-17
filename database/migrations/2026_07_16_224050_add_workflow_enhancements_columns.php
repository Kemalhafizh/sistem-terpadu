<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workflow_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
            $table->timestamp('due_at')->nullable()->after('notes');
        });

        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->string('decision')->default('pending')->change();
            $table->foreignId('delegated_to_id')->nullable()->after('approver_id')->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_requests', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });

        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->dropForeign(['delegated_to_id']);
            $table->dropColumn(['delegated_to_id', 'due_at']);
        });
    }
};
