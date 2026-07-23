<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Webhook Subscriptions Table
        Schema::create('webhook_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('target_url');
            $table->string('secret_key');
            $table->json('events'); // e.g. ["student.registered", "billing.paid", "grade.published"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Webhook Deliveries Table
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_subscription_id')->constrained('webhook_subscriptions')->onDelete('cascade');
            $table->string('event_type');
            $table->json('payload');
            $table->string('signature'); // HMAC-SHA256
            $table->string('status')->default('SENT'); // SENT, FAILED, RETRYING
            $table->unsignedInteger('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedInteger('attempt_count')->default(1);
            $table->timestamps();

            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_subscriptions');
    }
};
