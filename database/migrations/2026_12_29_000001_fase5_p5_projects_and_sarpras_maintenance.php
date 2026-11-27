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
        if (! Schema::hasTable('p5_projects')) {
            Schema::create('p5_projects', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('school_year_id')->nullable()->constrained('school_years')->nullOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
                $table->string('grade_level', 8)->index();
                $table->string('phase', 4)->default('E');
                $table->string('theme', 64);
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status', 24)->default('draft')->index();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('p5_project_dimensions')) {
            Schema::create('p5_project_dimensions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
                $table->foreignId('character_dimension_id')->constrained('character_dimensions')->cascadeOnDelete();
                $table->string('element', 160);
                $table->string('sub_element', 160)->nullable();
                $table->text('target_achievement');
                $table->unsignedSmallInteger('display_order')->default(1);
                $table->timestamps();

                $table->unique(['p5_project_id', 'character_dimension_id', 'element'], 'p5_proj_dim_elem_unique');
            });
        }

        if (! Schema::hasTable('p5_project_facilitators')) {
            Schema::create('p5_project_facilitators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['p5_project_id', 'user_id', 'classroom_id'], 'p5_proj_facilitator_unique');
            });
        }

        if (! Schema::hasTable('p5_project_assessments')) {
            Schema::create('p5_project_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
                $table->foreignId('p5_project_dimension_id')->constrained('p5_project_dimensions')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
                $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('predicate', 8);
                $table->timestamps();

                $table->unique(['p5_project_dimension_id', 'student_id'], 'p5_dim_student_unique');
                $table->index(['p5_project_id', 'student_id']);
            });
        }

        if (! Schema::hasTable('p5_project_student_notes')) {
            Schema::create('p5_project_student_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('process_notes');
                $table->timestamps();

                $table->unique(['p5_project_id', 'student_id'], 'p5_proj_student_note_unique');
            });
        }

        Schema::table('asset_maintenance_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_maintenance_tickets', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'handled_by')) {
                $table->foreignId('handled_by')->nullable()->after('reported_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'photo_disk')) {
                $table->string('photo_disk', 24)->default('local')->after('photo_evidence');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'cost')) {
                $table->decimal('cost', 15, 2)->nullable()->after('resolution_notes');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'vendor')) {
                $table->string('vendor', 160)->nullable()->after('cost');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'investigated_at')) {
                $table->timestamp('investigated_at')->nullable()->after('vendor');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'repair_started_at')) {
                $table->timestamp('repair_started_at')->nullable()->after('investigated_at');
            }
            if (! Schema::hasColumn('asset_maintenance_tickets', 'asset_maintenance_id')) {
                $table->foreignId('asset_maintenance_id')->nullable()->after('resolved_at')->constrained('asset_maintenances')->nullOnDelete();
            }
        });

        $existingTickets = DB::table('asset_maintenance_tickets')->whereNull('uuid')->get(['id', 'photo_evidence']);
        foreach ($existingTickets as $row) {
            DB::table('asset_maintenance_tickets')
                ->where('id', $row->id)
                ->update([
                    'uuid' => (string) Str::uuid(),
                    'photo_disk' => $row->photo_evidence ? 'public' : 'local',
                ]);
        }

        if (Schema::hasTable('positions')) {
            $exists = DB::table('positions')->where('code', 'koordinator_p5')->first();
            if (! $exists) {
                $positionId = DB::table('positions')->insertGetId([
                    'code' => 'koordinator_p5',
                    'name' => 'Koordinator Projek P5',
                    'description' => 'Mengelola tema, dimensi, fasilitator, dan rapor Projek Penguatan Profil Pelajar Pancasila (Kurikulum Merdeka).',
                    'allowed_account_types' => json_encode(['guru']),
                    'is_system' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (Schema::hasTable('position_permissions')) {
                    DB::table('position_permissions')->insertOrIgnore([
                        'position_id' => $positionId,
                        'permission' => 'kurikulum.kelola_p5',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('positions')) {
            DB::table('positions')->where('code', 'koordinator_p5')->delete();
        }

        Schema::table('asset_maintenance_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('asset_maintenance_tickets', 'uuid')) {
                $table->dropUnique('asset_maintenance_tickets_uuid_unique');
            }
            foreach (['handled_by', 'asset_maintenance_id'] as $fk) {
                if (Schema::hasColumn('asset_maintenance_tickets', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach (['uuid', 'photo_disk', 'cost', 'vendor', 'investigated_at', 'repair_started_at'] as $col) {
                if (Schema::hasColumn('asset_maintenance_tickets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('p5_project_student_notes');
        Schema::dropIfExists('p5_project_assessments');
        Schema::dropIfExists('p5_project_facilitators');
        Schema::dropIfExists('p5_project_dimensions');
        Schema::dropIfExists('p5_projects');
    }
};
