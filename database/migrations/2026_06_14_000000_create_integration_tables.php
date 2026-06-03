<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();                 // 'dapodik', 'emis', 'yayasan'
            $table->string('name');
            $table->text('credentials')->nullable();              // Encrypted credentials
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id')->constrained('integration_configs')->cascadeOnDelete();
            $table->string('direction');                          // 'export', 'import', 'sync'
            $table->string('status');                             // 'success', 'partial', 'failed'
            $table->integer('records_processed')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('error_details')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('url');
            $table->string('secret')->nullable();
            $table->json('events');                               // ['student.created', 'billing.paid']
            $table->boolean('is_active')->default(true);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('integration_configs');
    }
};
