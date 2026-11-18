<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 32)->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 24)->default('draf')->index();
            $table->timestamps();
        });

        Schema::table('academic_years', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_years', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->after('id')->constrained('school_years')->nullOnDelete();
            }
        });

        Schema::table('classrooms', function (Blueprint $table) {
            if (! Schema::hasColumn('classrooms', 'school_year_id')) {
                $table->foreignId('school_year_id')->nullable()->after('id')->constrained('school_years')->nullOnDelete();
            }
        });

        Schema::create('academic_calendars', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_year_id')->unique()->constrained('school_years')->cascadeOnDelete();
            $table->string('status', 24)->default('draf')->index();
            $table->boolean('needs_review')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::table('academic_events', function (Blueprint $table) {
            if (! Schema::hasColumn('academic_events', 'academic_calendar_id')) {
                $table->foreignId('academic_calendar_id')->nullable()->after('academic_year_id')->constrained('academic_calendars')->nullOnDelete();
            }
            if (! Schema::hasColumn('academic_events', 'milestone')) {
                $table->string('milestone', 64)->nullable()->after('event_type')->index();
            }
        });

        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->string('status', 32)->default('aktif')->index();
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->string('ended_reason', 120)->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('promotion_decision_id')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['school_year_id', 'classroom_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'display_group')) {
                $table->string('display_group', 120)->nullable()->after('category');
            }
        });

        Schema::create('classroom_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['classroom_id', 'subject_id']);
        });

        Schema::create('elective_surveys', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->string('title');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->unsignedTinyInteger('min_choices')->default(4);
            $table->unsignedTinyInteger('max_choices')->default(4);
            $table->boolean('ranked')->default(false);
            $table->string('status', 24)->default('draf')->index();
            $table->timestamp('announced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('elective_survey_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elective_survey_id')->constrained('elective_surveys')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['elective_survey_id', 'subject_id'], 'elective_survey_subject_unique');
        });

        Schema::create('elective_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elective_survey_id')->constrained('elective_surveys')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->timestamps();

            $table->unique(['elective_survey_id', 'student_id', 'subject_id'], 'elective_choice_unique');
        });

        Schema::create('class_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('placed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['school_year_id', 'student_id']);
        });

        Schema::create('promotion_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('from_school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->foreignId('to_school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->string('status', 24)->default('draf')->index();
            $table->unsignedBigInteger('meeting_note_id')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('held_at')->nullable();
            $table->text('hold_reason')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('promotion_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_batch_id')->constrained('promotion_batches')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('decision', 24)->nullable();
            $table->foreignId('to_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->json('basis')->nullable();
            $table->text('homeroom_note')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['promotion_batch_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_decisions');
        Schema::dropIfExists('promotion_batches');
        Schema::dropIfExists('class_placements');
        Schema::dropIfExists('elective_choices');
        Schema::dropIfExists('elective_survey_subjects');
        Schema::dropIfExists('elective_surveys');
        Schema::dropIfExists('classroom_subjects');

        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'display_group')) {
                $table->dropColumn('display_group');
            }
        });

        Schema::dropIfExists('class_enrollments');

        Schema::table('academic_events', function (Blueprint $table) {
            if (Schema::hasColumn('academic_events', 'academic_calendar_id')) {
                $table->dropConstrainedForeignId('academic_calendar_id');
            }
            if (Schema::hasColumn('academic_events', 'milestone')) {
                $table->dropColumn('milestone');
            }
        });

        Schema::dropIfExists('academic_calendars');

        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'school_year_id')) {
                $table->dropConstrainedForeignId('school_year_id');
            }
        });

        Schema::table('academic_years', function (Blueprint $table) {
            if (Schema::hasColumn('academic_years', 'school_year_id')) {
                $table->dropConstrainedForeignId('school_year_id');
            }
        });

        Schema::dropIfExists('school_years');
    }
};
