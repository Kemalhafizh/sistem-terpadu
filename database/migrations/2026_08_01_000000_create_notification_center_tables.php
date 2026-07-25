<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_category'); // academic, finance, system, discipline, hr
            $table->boolean('channel_inapp')->default(true);
            $table->boolean('channel_email')->default(true);
            $table->boolean('channel_whatsapp')->default(false);
            $table->boolean('channel_push')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'event_category']);
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->enum('channel', ['inapp', 'email', 'whatsapp', 'push'])->default('inapp');
            $table->string('event_type'); // e.g., academic.grade_published, finance.spp_due, system.alert
            $table->string('event_category')->default('system'); // academic, finance, system, discipline, hr
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
    }
};
