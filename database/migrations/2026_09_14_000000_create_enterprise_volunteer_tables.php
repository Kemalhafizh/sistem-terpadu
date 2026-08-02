<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('required_volunteers')->default(1);
            $table->integer('required_hours')->default(2);
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->enum('status', ['open', 'closed', 'completed'])->default('open');
            $table->foreignId('organizer_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('volunteer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('volunteer_opportunities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('hours_logged');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_logs');
        Schema::dropIfExists('volunteer_opportunities');
    }
};
