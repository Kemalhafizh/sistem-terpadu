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
        Schema::table('smart_alerts', function (Blueprint $table) {
            $table->foreignId('escalated_to_id')->nullable()->after('resolved_notes')->constrained('users')->nullOnDelete();
            $table->text('escalation_notes')->nullable()->after('escalated_to_id');
            $table->timestamp('escalated_at')->nullable()->after('escalation_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smart_alerts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('escalated_to_id');
            $table->dropColumn(['escalation_notes', 'escalated_at']);
        });
    }
};
