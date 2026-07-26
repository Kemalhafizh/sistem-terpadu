<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('release_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('release_type')->default('minor');
            $table->text('changelog_summary');
            $table->json('breaking_changes')->nullable();
            $table->integer('migration_count')->default(0);
            $table->string('released_by');
            $table->timestamp('released_at')->useCurrent();
            $table->boolean('rollback_tested')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_versions');
    }
};
