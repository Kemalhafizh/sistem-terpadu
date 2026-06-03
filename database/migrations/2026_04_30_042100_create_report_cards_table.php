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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('classroom_id')->constrained();

            // Kompilasi Nilai (Otomatis dihitung saat di-generate)
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->integer('rank_in_class')->nullable();

            // Catatan
            $table->text('homeroom_notes')->nullable(); // Catatan Wali Kelas
            $table->text('extracurricular_notes')->nullable(); // Catatan Ekstrakurikuler

            $table->foreignId('recorded_by')->nullable()->constrained('users');
            $table->timestamps();

            // Mencegah duplikasi 1 murid 1 rapor per semester
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
