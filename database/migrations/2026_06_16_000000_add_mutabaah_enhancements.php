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
        // 1. Add detailed columns to mutabaah_logs
        Schema::table('mutabaah_logs', function (Blueprint $table) {
            $table->foreignId('mutabaah_item_id')->nullable()->change();

            $table->tinyInteger('sholat_subuh')->default(0);       // 0=tidak, 1=ya, 2=berjamaah
            $table->tinyInteger('sholat_dzuhur')->default(0);
            $table->tinyInteger('sholat_ashar')->default(0);
            $table->tinyInteger('sholat_maghrib')->default(0);
            $table->tinyInteger('sholat_isya')->default(0);
            $table->tinyInteger('sholat_dhuha')->default(0);
            $table->tinyInteger('sholat_tahajud')->default(0);
            $table->integer('tadarus_pages')->default(0);          // Jumlah halaman Al-Qur'an yang dibaca
            $table->boolean('puasa_sunnah')->default(false);
            $table->boolean('sedekah')->default(false);
            $table->boolean('dzikir_pagi')->default(false);
            $table->boolean('dzikir_sore')->default(false);
            $table->text('catatan_harian')->nullable();             // Refleksi hari ini
            $table->integer('score')->default(0);                   // Auto-calculated score (0-100)
        });

        // 2. Create mutabaah_weekly_summaries
        Schema::create('mutabaah_weekly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->integer('total_score');
            $table->integer('sholat_5_waktu_count');             // Berapa kali sholat 5 waktu lengkap
            $table->integer('sholat_berjamaah_count');
            $table->integer('tadarus_total_pages');
            $table->enum('grade', ['A', 'B', 'C', 'D'])->nullable(); // A=istimewa, D=perlu pembinaan
            $table->text('wali_kelas_comment')->nullable();
            $table->boolean('parent_acknowledged')->default(false); // Ortu sudah melihat/konfirmasi
            $table->timestamps();

            $table->unique(['student_id', 'week_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutabaah_weekly_summaries');

        Schema::table('mutabaah_logs', function (Blueprint $table) {
            $table->foreignId('mutabaah_item_id')->nullable(false)->change();

            $table->dropColumn([
                'sholat_subuh',
                'sholat_dzuhur',
                'sholat_ashar',
                'sholat_maghrib',
                'sholat_isya',
                'sholat_dhuha',
                'sholat_tahajud',
                'tadarus_pages',
                'puasa_sunnah',
                'sedekah',
                'dzikir_pagi',
                'dzikir_sore',
                'catatan_harian',
                'score',
            ]);
        });
    }
};
