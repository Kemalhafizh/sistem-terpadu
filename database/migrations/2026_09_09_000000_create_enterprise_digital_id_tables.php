<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_id_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('card_hash')->unique();
            $table->string('nfc_uid')->nullable()->unique();
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('digital_id_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('digital_id_cards')->cascadeOnDelete();
            $table->string('location');
            $table->string('scan_type');
            $table->timestamp('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_id_scans');
        Schema::dropIfExists('digital_id_cards');
    }
};
