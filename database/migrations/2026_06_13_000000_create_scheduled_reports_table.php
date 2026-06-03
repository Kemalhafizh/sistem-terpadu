<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('report_type'); // 'daily_admin', 'weekly_kepsek', 'monthly_finance', 'weekly_parent'
            $table->string('cron_expression'); // e.g. '0 7 * * *' (daily at 7 AM)
            $table->json('recipients'); // array of emails or roles: [{"type":"email","value":"admin@smaisa1.sch.id"}]
            $table->string('delivery_method')->default('email'); // 'email', 'whatsapp', 'both'
            $table->boolean('attach_pdf')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
