<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_sla_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_key');
            $table->string('vendor_name');
            $table->string('endpoint_url');
            $table->string('status')->default('UP'); // UP, DEGRADED, DOWN
            $table->float('latency_ms')->default(0);
            $table->unsignedInteger('http_status')->default(200);
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['vendor_key', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_sla_metrics');
    }
};
