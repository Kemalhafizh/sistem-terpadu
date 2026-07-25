<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 31.1 API Clients Table
        Schema::create('api_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client_id')->unique();
            $table->string('client_secret');
            $table->json('allowed_scopes')->nullable();
            $table->integer('rate_limit_per_minute')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 31.2 ETL Jobs Table
        Schema::create('etl_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('source_type', ['dapodik_xml', 'emis_json', 'excel', 'csv'])->default('excel');
            $table->string('source_file_path');
            $table->string('target_table');
            $table->json('mapping_config')->nullable();
            $table->json('validation_errors')->nullable();
            $table->enum('status', ['pending', 'extracting', 'transforming', 'loading', 'completed', 'failed'])->default('pending');
            $table->integer('records_total')->default(0);
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 31.3 Plugins Table
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version');
            $table->string('author');
            $table->string('entry_class');
            $table->json('config')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('installed_at')->useCurrent();
            $table->timestamps();
        });

        // 31.3 Plugin Hooks Table
        Schema::create('plugin_hooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained('plugins')->cascadeOnDelete();
            $table->enum('hook_name', ['sidebar.menu', 'dashboard.widget', 'student.profile.tab', 'settings.panel'])->default('dashboard.widget');
            $table->integer('priority')->default(10);
            $table->string('handler_class');
            $table->string('handler_method');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_hooks');
        Schema::dropIfExists('plugins');
        Schema::dropIfExists('etl_jobs');
        Schema::dropIfExists('api_clients');
    }
};
