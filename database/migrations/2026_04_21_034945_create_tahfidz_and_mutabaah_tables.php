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
        // 1. Tahfidz Logs
        Schema::create('tahfidz_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->integer('surah_number'); // 1-114
            $table->string('surah_name');
            $table->integer('from_ayat');
            $table->integer('to_ayat');
            $table->enum('type', ['ziyadah', 'murajaah'])->default('ziyadah');
            $table->string('grade')->nullable(); // A, B, C, etc.
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();
        });

        // 2. Mutaba'ah Items (Master Data)
        Schema::create('mutabaah_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Shalat Subuh Berjamaah
            $table->enum('category', ['wajib', 'sunnah', 'akhlak', 'target_padi'])->default('wajib');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Mutaba'ah Logs
        Schema::create('mutabaah_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('mutabaah_item_id')->constrained()->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->date('date');
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Parent or Teacher
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutabaah_logs');
        Schema::dropIfExists('mutabaah_items');
        Schema::dropIfExists('tahfidz_logs');
    }
};
