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
        Schema::create('sync_configs', function (Blueprint $table) {
            $table->id();
            $table->string('system_target'); // "dapodik" or "emis"
            $table->string('local_table');   // "students", "teachers"
            $table->json('field_mapping');   // Map local_col => target_col
            $table->timestamps();

            $table->unique(['system_target', 'local_table']);
        });

        Schema::create('sync_reports', function (Blueprint $table) {
            $table->id();
            $table->string('system_target');
            $table->string('local_table');
            $table->timestamp('sync_run_at');
            $table->integer('records_processed');
            $table->integer('records_success');
            $table->integer('records_failed');
            $table->json('error_logs')->nullable();
            $table->foreignId('run_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_reports');
        Schema::dropIfExists('sync_configs');
    }
};
