<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immutable_audit_chains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sequence')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('payload');
            $table->string('previous_hash', 64);
            $table->string('entry_hash', 64)->unique();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immutable_audit_chains');
    }
};
