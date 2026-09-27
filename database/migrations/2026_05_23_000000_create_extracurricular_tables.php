<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Ekstrakurikuler
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                 // "Pramuka", "Paskibra", "Robotik", "Futsal"
            $table->text('description')->nullable();
            $table->foreignId('advisor_id')->constrained('users')->cascadeOnDelete(); // Guru Pembina
            $table->string('schedule_day')->nullable();             // "Senin", "Jumat"
            $table->string('schedule_time')->nullable();            // "14:00 - 16:00"
            $table->string('location')->nullable();                 // "Lapangan Utama", "Lab Fisika"
            $table->integer('max_members')->nullable();             // Batas kuota anggota (null = unlimited)
            $table->boolean('is_active')->default(true);
            $table->string('cover_image')->nullable();              // Foto sampul ekskul
            $table->timestamps();

            $table->index(['advisor_id', 'is_active']);
        });

        // 2. Tabel Pivot Anggota Ekstrakurikuler
        Schema::create('extracurricular_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('role', ['anggota', 'ketua', 'wakil_ketua', 'sekretaris', 'bendahara'])->default('anggota');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('joined_at');
            $table->timestamps();

            $table->unique(['extracurricular_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });

        // 3. Tabel Presensi Latihan Ekskul
        Schema::create('extracurricular_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->enum('status', ['H', 'S', 'I', 'A'])->default('H'); // Hadir, Sakit, Izin, Alpa
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'date', 'student_id'], 'ekskul_att_unique');
            $table->index(['extracurricular_id', 'date']);
        });

        // 4. Tabel Dokumentasi & Laporan Kegiatan Ekskul
        Schema::create('extracurricular_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->string('title');                                 // "Latihan Rutin LKBB", "Penyusunan Program Kerja"
            $table->text('description')->nullable();
            $table->date('activity_date');
            $table->json('gallery_paths')->nullable();              // Array berkas foto ["uploads/activity/pic1.jpg", ...]
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete(); // Pembuat laporan (Guru Pembina)
            $table->timestamps();

            $table->index(['extracurricular_id', 'activity_date']);
        });

        // 5. Tabel Transaksi Kas OSIS
        Schema::create('osis_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);            // Pemasukan / Pengeluaran
            $table->decimal('amount', 15, 2);                       // Nominal
            $table->string('description');                           // "Iuran Bulanan Siswa X", "Pembelian Bola Basket"
            $table->string('category')->nullable();                 // "Iuran", "Sponsor", "Kegiatan", "Perlengkapan", "Lainnya"
            $table->date('transaction_date');
            $table->string('receipt_path')->nullable();              // Path berkas scan kuitansi/bukti transaksi
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete(); // Pencatat transaksi
            $table->timestamps();

            $table->index(['type', 'transaction_date']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('osis_transactions');
        Schema::dropIfExists('extracurricular_activities');
        Schema::dropIfExists('extracurricular_attendances');
        Schema::dropIfExists('extracurricular_members');
        Schema::dropIfExists('extracurriculars');
    }
};
