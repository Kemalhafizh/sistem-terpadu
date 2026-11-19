<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_card_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('report_card_templates', 'report_type')) {
                $table->string('report_type', 16)->default('pas')->after('phase');
            }
            if (! Schema::hasColumn('report_card_templates', 'semester_number')) {
                $table->unsignedTinyInteger('semester_number')->nullable()->after('semester');
            }
            if (! Schema::hasColumn('report_card_templates', 'status')) {
                $table->string('status', 40)->default('nilai_disusun')->after('is_published')->index();
            }
            if (! Schema::hasColumn('report_card_templates', 'issued_date')) {
                $table->date('issued_date')->nullable()->after('status');
            }
            if (! Schema::hasColumn('report_card_templates', 'grade_level')) {
                $table->string('grade_level', 10)->nullable()->after('issued_date');
            }
            if (! Schema::hasColumn('report_card_templates', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('grade_level');
            }
            if (! Schema::hasColumn('report_card_templates', 'published_by')) {
                $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('report_card_pdfs', function (Blueprint $table) {
            if (! Schema::hasColumn('report_card_pdfs', 'is_official')) {
                $table->boolean('is_official')->default(false)->after('qr_verification_url');
            }
            if (! Schema::hasColumn('report_card_pdfs', 'original_filename')) {
                $table->string('original_filename')->nullable()->after('is_official');
            }
        });

        Schema::table('daily_assessments', function (Blueprint $table) {
            if (! Schema::hasColumn('daily_assessments', 'learning_objective')) {
                $table->string('learning_objective', 255)->nullable()->after('description');
            }
        });

        Schema::table('letters', function (Blueprint $table) {
            $table->string('status', 32)->default('draft')->change();

            if (! Schema::hasColumn('letters', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('letters', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('letters', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('letters', 'verification_token')) {
                $table->string('verification_token', 64)->nullable()->unique()->after('approved_at');
            }
        });

        if (! Schema::hasTable('incoming_letters')) {
            Schema::create('incoming_letters', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('agenda_number')->unique();
                $table->string('reference_number');
                $table->string('sender');
                $table->string('subject');
                $table->date('letter_date');
                $table->date('received_date');
                $table->string('category', 64)->nullable();
                $table->text('summary')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('status', 32)->default('diterima')->index();
                $table->foreignId('created_by')->constrained('users');
                $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('letter_dispositions')) {
            Schema::create('letter_dispositions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('incoming_letter_id')->constrained('incoming_letters')->cascadeOnDelete();
                $table->foreignId('from_user_id')->constrained('users');
                $table->foreignId('to_user_id')->constrained('users');
                $table->text('instruction');
                $table->string('priority', 24)->default('normal');
                $table->date('due_at')->nullable();
                $table->string('status', 32)->default('didisposisikan')->index();
                $table->text('follow_up_notes')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['to_user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_dispositions');
        Schema::dropIfExists('incoming_letters');

        Schema::table('letters', function (Blueprint $table) {
            foreach (['approved_by'] as $fk) {
                if (Schema::hasColumn('letters', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach (['submitted_at', 'approved_at', 'verification_token'] as $col) {
                if (Schema::hasColumn('letters', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('daily_assessments', function (Blueprint $table) {
            if (Schema::hasColumn('daily_assessments', 'learning_objective')) {
                $table->dropColumn('learning_objective');
            }
        });

        Schema::table('report_card_pdfs', function (Blueprint $table) {
            foreach (['is_official', 'original_filename'] as $col) {
                if (Schema::hasColumn('report_card_pdfs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('report_card_templates', function (Blueprint $table) {
            if (Schema::hasColumn('report_card_templates', 'published_by')) {
                $table->dropConstrainedForeignId('published_by');
            }
            foreach (['report_type', 'semester_number', 'status', 'issued_date', 'grade_level', 'published_at'] as $col) {
                if (Schema::hasColumn('report_card_templates', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
