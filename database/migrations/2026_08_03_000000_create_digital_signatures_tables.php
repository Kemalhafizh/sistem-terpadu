<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category'); // academic, hr, finance, official
            $table->string('blade_template_path');
            $table->json('required_signers');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // e.g. Rapor, SuratKeputusan, Kwitansi, SuratIzin
            $table->unsignedBigInteger('document_id');
            $table->foreignId('signer_id')->constrained('users')->cascadeOnDelete();
            $table->string('signer_role'); // Kepsek, Wakasek, Bendahara, WaliKelas
            $table->string('signature_hash')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('qr_verification_code')->unique();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('document_templates');
    }
};
