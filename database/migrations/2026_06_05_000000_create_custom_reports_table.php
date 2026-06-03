<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('config');                       // source, columns, filters, groupBy, orderBy, chartType
            $table->boolean('is_shared')->default(false);
            $table->string('schedule_cron')->nullable();   // Cron expression for automated delivery
            $table->json('schedule_recipients')->nullable(); // Target email array
            $table->timestamps();

            $table->index('created_by');
            $table->index('is_shared');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
