<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('environment')->default('production');
            $table->string('config_group')->default('general');
            $table->string('config_key');
            $table->text('config_value_encrypted');
            $table->text('description')->nullable();
            $table->boolean('is_sensitive')->default(false);
            $table->string('last_updated_by')->nullable();
            $table->timestamps();
            $table->unique(['environment', 'config_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
