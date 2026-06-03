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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            // Nomor pendaftaran unik (Misal: SPMB-2026-0001)
            $table->string('registration_number')->unique();

            // Biodata Dasar
            $table->string('full_name');
            $table->string('nisn')->nullable();
            $table->string('previous_school');
            $table->string('parent_phone');

            // Berkas (Path ke Storage)
            $table->string('document_kk')->nullable();
            $table->string('document_certificate')->nullable();

            // Status Pendaftaran
            $table->enum('status', [
                'pending', // Baru daftar
                'reviewing', // Sedang dicek berkas
                'accepted', // Diterima
                'rejected', // Ditolak
            ])->default('pending');

            // Relasi
            $table->foreignId('academic_year_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
