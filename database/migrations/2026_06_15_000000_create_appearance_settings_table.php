<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appearance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Sistem Terpadu SMA ISA 1');
            $table->string('logo_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('primary_color')->default('#3b82f6');
            $table->string('secondary_color')->default('#8b5cf6');
            $table->string('accent_color')->default('#06b6d4');
            $table->string('sidebar_color')->default('#0f172a');
            $table->string('font_family')->default('Inter');
            $table->text('custom_css')->nullable();
            $table->string('login_background_path')->nullable();
            $table->string('footer_text')->default('© 2026 SMA Islam Sultan Agung 1 Semarang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appearance_settings');
    }
};
