<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('graduation_batches')) {
            Schema::create('graduation_batches', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('school_year_id')->unique()->constrained('school_years')->cascadeOnDelete();
                $table->unsignedSmallInteger('graduation_year');
                $table->string('sk_number', 120)->nullable();
                $table->date('sk_date')->nullable();
                $table->date('graduation_date')->nullable();
                $table->foreignId('meeting_note_id')->nullable()->constrained('meeting_notes')->nullOnDelete();
                $table->string('status', 32)->default('draf')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamp('announced_at')->nullable();
                $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('executed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('graduation_decisions')) {
            Schema::create('graduation_decisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('graduation_batch_id')->constrained('graduation_batches')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->string('decision', 24)->nullable()->index();
                $table->json('basis')->nullable();
                $table->text('note')->nullable();
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['graduation_batch_id', 'student_id']);
            });
        }

        Schema::table('alumni_data', function (Blueprint $table) {
            if (! Schema::hasColumn('alumni_data', 'graduation_batch_id')) {
                $table->foreignId('graduation_batch_id')->nullable()->after('student_id')->constrained('graduation_batches')->nullOnDelete();
            }
            if (! Schema::hasColumn('alumni_data', 'sk_number')) {
                $table->string('sk_number', 120)->nullable()->after('graduation_year');
            }
            if (! Schema::hasColumn('alumni_data', 'graduation_date')) {
                $table->date('graduation_date')->nullable()->after('sk_number');
            }
        });

        Schema::table('student_mutations', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();

            if (! Schema::hasColumn('student_mutations', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('student_mutations', 'classroom_id')) {
                $table->foreignId('classroom_id')->nullable()->after('student_id')->constrained('classrooms')->nullOnDelete();
            }
            if (! Schema::hasColumn('student_mutations', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->after('classroom_id')->constrained('school_years')->nullOnDelete();
            }
            if (! Schema::hasColumn('student_mutations', 'letter_number')) {
                $table->string('letter_number', 120)->nullable()->after('target_school');
            }
            if (! Schema::hasColumn('student_mutations', 'status')) {
                $table->string('status', 32)->default('approved')->after('document_path')->index();
            }
            if (! Schema::hasColumn('student_mutations', 'candidate_data')) {
                $table->json('candidate_data')->nullable()->after('status');
            }
            if (! Schema::hasColumn('student_mutations', 'billing_review_status')) {
                $table->string('billing_review_status', 32)->nullable()->after('candidate_data');
            }
            if (! Schema::hasColumn('student_mutations', 'billing_review_summary')) {
                $table->json('billing_review_summary')->nullable()->after('billing_review_status');
            }
            if (! Schema::hasColumn('student_mutations', 'requested_by')) {
                $table->foreignId('requested_by')->nullable()->after('billing_review_summary')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('student_mutations', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('requested_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('student_mutations', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('student_mutations', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('student_mutations', 'rejection_notes')) {
                $table->text('rejection_notes')->nullable()->after('executed_at');
            }
        });

        // Backfill pre-existing historical mutation rows with UUID and approved status.
        $existingMutations = DB::table('student_mutations')->whereNull('uuid')->get(['id']);
        foreach ($existingMutations as $row) {
            DB::table('student_mutations')
                ->where('id', $row->id)
                ->update([
                    'uuid' => (string) Str::uuid(),
                    'status' => 'approved',
                ]);
        }

        Schema::table('warning_letters', function (Blueprint $table) {
            if (! Schema::hasColumn('warning_letters', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->after('issued_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('warning_letters', 'counseling_session_id')) {
                $table->foreignId('counseling_session_id')->nullable()->after('notes')->constrained('counseling_sessions')->nullOnDelete();
            }
            if (! Schema::hasColumn('warning_letters', 'parent_notified_at')) {
                $table->timestamp('parent_notified_at')->nullable()->after('counseling_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warning_letters', function (Blueprint $table) {
            foreach (['issued_by', 'counseling_session_id'] as $fk) {
                if (Schema::hasColumn('warning_letters', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            if (Schema::hasColumn('warning_letters', 'parent_notified_at')) {
                $table->dropColumn('parent_notified_at');
            }
        });

        Schema::table('student_mutations', function (Blueprint $table) {
            if (Schema::hasColumn('student_mutations', 'uuid')) {
                $table->dropUnique('student_mutations_uuid_unique');
            }
            if (Schema::hasColumn('student_mutations', 'status')) {
                $table->dropIndex('student_mutations_status_index');
            }
            foreach (['classroom_id', 'school_year_id', 'requested_by', 'approved_by'] as $fk) {
                if (Schema::hasColumn('student_mutations', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach ([
                'uuid',
                'letter_number',
                'status',
                'candidate_data',
                'billing_review_status',
                'billing_review_summary',
                'approved_at',
                'executed_at',
                'rejection_notes',
            ] as $col) {
                if (Schema::hasColumn('student_mutations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('alumni_data', function (Blueprint $table) {
            if (Schema::hasColumn('alumni_data', 'graduation_batch_id')) {
                $table->dropConstrainedForeignId('graduation_batch_id');
            }
            foreach (['sk_number', 'graduation_date'] as $col) {
                if (Schema::hasColumn('alumni_data', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('graduation_decisions');
        Schema::dropIfExists('graduation_batches');
    }
};
