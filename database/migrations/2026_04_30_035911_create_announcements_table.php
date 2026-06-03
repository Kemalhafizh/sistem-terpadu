<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image_path')->nullable(); // Untuk poster/foto

            // Penargetan
            $table->string('target_role')->nullable();
            $table->foreignId('target_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();

            // Pengaturan tayang
            $table->enum('priority', ['low', 'normal', 'urgent'])->default('normal');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
