<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dapodik_exports', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->string('semester');
            $table->string('export_file_path')->nullable();
            $table->enum('status', ['pending', 'validating', 'completed', 'failed'])->default('pending');
            $table->integer('records_processed')->default(0);
            $table->integer('error_count')->default(0);
            $table->foreignId('exported_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('dapodik_sync_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('export_id')->constrained('dapodik_exports')->cascadeOnDelete();
            $table->string('entity_type');
            $table->string('entity_identifier');
            $table->text('error_message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dapodik_sync_errors');
        Schema::dropIfExists('dapodik_exports');
    }
};
