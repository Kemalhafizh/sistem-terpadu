<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignId('imported_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_name');
            $table->integer('total_rows')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('error_details')->nullable();
            $table->json('created_ids')->nullable(); // Store created IDs: {"users": [1,2,3], "students": [4,5,6]}
            $table->enum('status', ['processing', 'completed', 'failed', 'rolled_back'])->default('processing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
