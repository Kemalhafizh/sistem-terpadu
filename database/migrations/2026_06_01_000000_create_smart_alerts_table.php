<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('smart_alerts');
        Schema::create('smart_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');                  // 'at_risk_student', 'attendance_decline', 'payment_default', 'journal_gap', 'critical_medicine', 'overdue_asset'
            $table->string('severity');              // 'info', 'warning', 'critical'
            $table->string('title');                 // Ringkasan judul alert
            $table->text('description');             // Deskripsi detail & rekomendasi awal
            $table->json('context_data')->nullable(); // Payload data pendukung (misal: rerata nilai, daftar tagihan, dll.)
            $table->morphs('alertable');             // Relasi polimorfik ke objek terkait (Student, User, UksMedicine, AssetLoan)
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');

            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();

            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable(); // Catatan penanganan masalah

            $table->timestamps();

            // Optimasi indeks untuk query dasbor interaktif
            $table->index(['type', 'status']);
            $table->index(['severity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_alerts');
    }
};
