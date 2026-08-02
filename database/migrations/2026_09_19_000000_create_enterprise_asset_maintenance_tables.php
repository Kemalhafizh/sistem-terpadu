<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('location');
            $table->enum('condition', ['excellent', 'good', 'needs_repair', 'broken'])->default('good');
            $table->date('purchase_date')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('facility_assets')->cascadeOnDelete();
            $table->foreignId('technician_id')->constrained('users');
            $table->string('service_type');
            $table->text('findings')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->date('service_date');
            $table->date('next_service_due')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('facility_assets');
    }
};
