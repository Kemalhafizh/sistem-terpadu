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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('classroom_id')->constrained(); // Kelas saat diabsen

            // Nullable: Jika kosong, berarti ini Presensi Harian (Wali Kelas)
            // Jika ada isinya, berarti ini Presensi Mapel (Guru Mapel)
            $table->foreignId('schedule_id')->nullable()->constrained()->cascadeOnDelete();

            $table->date('date'); // Tanggal presensi

            // Status kehadiran (H=Hadir, S=Sakit, I=Izin, A=Alpa)
            $table->enum('status', ['H', 'S', 'I', 'A'])->default('H');

            $table->text('notes')->nullable(); // Keterangan

            // Siapa yang mengabsen?
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            // Mencegah duplikasi absen harian untuk murid yang sama (jika bukan absen mapel)
            // Untuk mencegah duplikasi total, kita tidak buat unique constraint di DB agar fleksibel.
            // Kita akan tangani validasi uniknya di layer Controller.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
