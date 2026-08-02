<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnerships', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->enum('partner_type', ['university', 'corporate', 'government', 'ngo', 'community'])->default('corporate');
            $table->string('mou_document_path')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_email')->nullable();
            $table->enum('status', ['active', 'expired', 'pending'])->default('active');
            $table->date('valid_until')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('partnership_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partnership_id')->constrained('partnerships')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->integer('participant_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_activities');
        Schema::dropIfExists('partnerships');
    }
};
