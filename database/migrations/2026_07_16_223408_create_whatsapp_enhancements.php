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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['keuangan', 'akademik', 'keislaman', 'kegiatan', 'umum']);
            $table->text('content');
            $table->json('placeholders')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('whatsapp_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('target_type', ['all_parents', 'all_teachers', 'specific_class', 'custom']);
            $table->json('target_ids')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('whatsapp_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('trigger', [
                'billing_due_3_days',
                'billing_overdue',
                'attendance_absent',
                'exam_upcoming_1_day',
                'uks_visit_severe',
                'grade_below_kkm',
                'mutabaah_grade_d',
                'tahfidz_behind_target',
            ]);
            $table->foreignId('template_id')->constrained('whatsapp_templates')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_automations');
        Schema::dropIfExists('whatsapp_broadcasts');
        Schema::dropIfExists('whatsapp_templates');
    }
};
