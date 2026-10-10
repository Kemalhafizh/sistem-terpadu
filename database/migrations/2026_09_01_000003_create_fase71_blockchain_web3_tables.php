<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blockchain_credentials')) {
            Schema::create('blockchain_credentials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('credential_type', 64); // diploma, transcript, achievement, tahfidz_sanad
                $table->string('certificate_number', 128)->unique();
                $table->string('recipient_name', 255);
                $table->string('merkle_root', 66); // 0x...
                $table->string('transaction_hash', 66); // 0x...
                $table->string('contract_address', 42); // 0x...
                $table->unsignedBigInteger('block_number')->nullable();
                $table->timestamp('issued_at');
                $table->timestamp('revoked_at')->nullable();
                $table->json('metadata_json');
                $table->timestamps();

                $table->index(['student_id', 'credential_type']);
                $table->index('certificate_number');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blockchain_credentials');
    }
};
