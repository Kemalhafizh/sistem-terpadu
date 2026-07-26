<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accessibility_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('page_route');
            $table->string('audit_type')->default('wcag22_aa');
            $table->integer('issues_found')->default(0);
            $table->integer('issues_resolved')->default(0);
            $table->string('wcag_level')->default('AA+');
            $table->string('audited_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessibility_audit_logs');
    }
};
