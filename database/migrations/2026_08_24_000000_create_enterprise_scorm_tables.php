<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorm_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('version', ['scorm12', 'scorm2004', 'xapi'])->default('scorm12');
            $table->string('file_path');
            $table->json('manifest_data')->nullable();
            $table->string('entry_point')->default('index.html');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['processing', 'ready', 'error'])->default('ready');
            $table->timestamps();
        });

        Schema::create('scorm_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scorm_package_id')->constrained('scorm_packages')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->enum('lesson_status', ['not_attempted', 'incomplete', 'completed', 'passed', 'failed'])->default('not_attempted');
            $table->float('score_raw')->nullable();
            $table->float('score_max')->nullable();
            $table->integer('total_time_seconds')->default(0);
            $table->text('suspend_data')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('xapi_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('verb');
            $table->string('object_type');
            $table->string('object_id');
            $table->json('result')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('timestamp_at')->useCurrent();
            $table->timestamp('stored_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xapi_statements');
        Schema::dropIfExists('scorm_tracking');
        Schema::dropIfExists('scorm_packages');
    }
};
