<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop tables if they already exist to handle half-run states gracefully
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_requests');
        Schema::dropIfExists('workflow_definitions');

        // 1. Definisi Template Alur Persetujuan
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Persetujuan Cuti", "Persetujuan Surat"
            $table->string('code')->unique();                 // "leave_approval", "letter_approval"
            $table->text('description')->nullable();
            $table->json('steps');                            // [{"order":1,"role":"waka_kurikulum"},{"order":2,"role":"kepala_sekolah"}]
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Request Pengerjaan Persetujuan Aktif
        Schema::create('workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('requestable');                    // Polimorfik ke model induk (LeaveRequest, Letter, dll)
            $table->integer('current_step')->default(1);
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();                // Catatan awal pengaju
            $table->timestamps();

            $table->index(['status', 'current_step']);
            $table->index('requester_id');
        });

        // 3. Log Riwayat Persetujuan per Langkah (Audit Trail)
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('workflow_requests')->cascadeOnDelete();
            $table->integer('step_order');
            $table->string('role');                           // Role penilai yang berwenang (misal: 'kepala_sekolah')
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('decision', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();              // Catatan review/alasan tolak
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['request_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
        Schema::dropIfExists('workflow_requests');
        Schema::dropIfExists('workflow_definitions');
    }
};
