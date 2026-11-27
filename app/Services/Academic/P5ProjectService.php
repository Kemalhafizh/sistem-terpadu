<?php

namespace App\Services\Academic;

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicYear;
use App\Models\CharacterDimension;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\P5Project;
use App\Models\P5ProjectAssessment;
use App\Models\P5ProjectDimension;
use App\Models\P5ProjectFacilitator;
use App\Models\P5ProjectStudentNote;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class P5ProjectService
{
    /**
     * Official 6 Dimensions of Profil Pelajar Pancasila (Kemendikdasmen Kurikulum Merdeka).
     *
     * @var list<array{name: string, description: string, display_order: int}>
     */
    public const OFFICIAL_P5_DIMENSIONS = [
        [
            'name' => 'Beriman, Bertakwa kepada Tuhan Yang Maha Esa, dan Berakhlak Mulia',
            'description' => 'Akhlak beragama, akhlak pribadi, akhlak kepada manusia, akhlak kepada alam, dan akhlak bernegara.',
            'display_order' => 1,
        ],
        [
            'name' => 'Berkebinekaan Global',
            'description' => 'Mengenal dan menghargai budaya, komunikasi interkultural, serta refleksi dan tanggung jawab terhadap kebinekaan.',
            'display_order' => 2,
        ],
        [
            'name' => 'Bergotong Royong',
            'description' => 'Kolaborasi, kepedulian, dan berbagi dalam kerja bersama.',
            'display_order' => 3,
        ],
        [
            'name' => 'Mandiri',
            'description' => 'Pemahaman diri dan situasi yang dihadapi serta regulasi diri.',
            'display_order' => 4,
        ],
        [
            'name' => 'Bernalar Kritis',
            'description' => 'Memperoleh dan memproses informasi dan gagasan, menganalisis serta mengevaluasi penalaran, dan merefleksi pemikiran.',
            'display_order' => 5,
        ],
        [
            'name' => 'Kreatif',
            'description' => 'Menghasilkan gagasan yang orisinal serta menghasilkan karya dan tindakan yang orisinal.',
            'display_order' => 6,
        ],
    ];

    /**
     * Ensure the 6 official Kemendikdasmen P5 dimensions exist in `character_dimensions`.
     *
     * @return Collection<int, CharacterDimension>
     */
    public function ensureOfficialDimensions(): Collection
    {
        foreach (self::OFFICIAL_P5_DIMENSIONS as $item) {
            CharacterDimension::firstOrCreate(
                [
                    'name' => $item['name'],
                    'category' => 'p5',
                ],
                [
                    'description' => $item['description'],
                    'icon_name' => 'book-open',
                    'display_order' => $item['display_order'],
                    'is_active' => true,
                ]
            );
        }

        return CharacterDimension::query()
            ->where('category', 'p5')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * Check whether the user is authorized to create/configure P5 projects (`ALUR_KERJA.md` D4:
     * Koordinator P5 di bawah Waka Kurikulum).
     */
    public function canManageProjects(User $user, ?P5Project $project = null): bool
    {
        if (
            $user->hasPermission('kurikulum.kelola_p5')
            || $user->hasPermission('kurikulum.kelola')
            || $user->hasPermission('akademik.kelola_struktur')
            || RoleMiddleware::allowsUser($user, ['waka_kurikulum', 'koordinator_p5', 'admin', 'superadmin'])
        ) {
            return true;
        }

        if ($project !== null && $project->coordinator_id !== null && (int) $project->coordinator_id === (int) $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check whether `$user` may record P5 assessments/notes for `$student` on `$project`:
     * project manager/coordinator, assigned facilitator (project-wide or student's classroom),
     * or homeroom teacher of the student's classroom.
     */
    public function canAssessStudent(User $user, P5Project $project, Student $student): bool
    {
        if ($this->canManageProjects($user, $project)) {
            return true;
        }

        $student->loadMissing('classroom');
        $classroomId = $student->classroom_id;

        if ($classroomId !== null && $student->classroom !== null && (int) $student->classroom->teacher_id === (int) $user->id) {
            return true;
        }

        return P5ProjectFacilitator::query()
            ->where('p5_project_id', $project->id)
            ->where('user_id', $user->id)
            ->where(function ($q) use ($classroomId) {
                $q->whereNull('classroom_id');
                if ($classroomId !== null) {
                    $q->orWhere('classroom_id', $classroomId);
                }
            })
            ->exists();
    }

    /**
     * Anti-IDOR gate for viewing/printing a student's P5 Report Card (`Rapor Projek P5`).
     */
    public function canViewStudentReport(User $user, Student $student): bool
    {
        if ($student->isVisibleTo($user)) {
            return true;
        }

        if ($this->canManageProjects($user)) {
            return true;
        }

        $student->loadMissing('classroom');
        if ($student->classroom !== null && (int) $student->classroom->teacher_id === (int) $user->id) {
            return true;
        }

        $isEnrollmentHomeroom = ClassEnrollment::query()
            ->where('student_id', $student->id)
            ->whereHas('classroom', fn ($cq) => $cq->where('teacher_id', $user->id))
            ->exists();
        if ($isEnrollmentHomeroom) {
            return true;
        }

        $gradeLevel = strtoupper(trim((string) ($student->classroom?->grade_level ?? '')));

        return P5Project::query()
            ->when($gradeLevel !== '', fn ($q) => $q->where('grade_level', $gradeLevel))
            ->where(function ($q) use ($user, $student) {
                $q->where('coordinator_id', $user->id)
                    ->orWhereHas('facilitators', function ($fq) use ($user, $student) {
                        $fq->where('user_id', $user->id)
                            ->where(function ($cq) use ($student) {
                                $cq->whereNull('classroom_id');
                                if ($student->classroom_id !== null) {
                                    $cq->orWhere('classroom_id', $student->classroom_id);
                                }
                            });
                    });
            })
            ->exists();
    }

    /**
     * Create a new P5 Project with mapped dimensions and optional facilitators.
     *
     * @param  array<string, mixed>  $data
     */
    public function createProject(User $actor, array $data): P5Project
    {
        if (! $this->canManageProjects($actor)) {
            abort(403, __('Hanya Koordinator P5 atau Waka Kurikulum yang berwenang membuat Projek P5.'));
        }

        return DB::transaction(function () use ($actor, $data) {
            $gradeLevel = strtoupper(trim((string) $data['grade_level']));
            if (! in_array($gradeLevel, ['X', 'XI', 'XII'], true)) {
                throw ValidationException::withMessages([
                    'grade_level' => __('Tingkat kelas Projek P5 harus X, XI, atau XII.'),
                ]);
            }

            $schoolYearId = $data['school_year_id'] ?? null;
            $academicYearId = $data['academic_year_id'] ?? null;

            if (! $schoolYearId && $academicYearId) {
                $schoolYearId = AcademicYear::whereKey($academicYearId)->value('school_year_id');
            }
            if (! $schoolYearId) {
                $schoolYearId = SchoolYear::where('status', SchoolYear::STATUS_ACTIVE)->value('id')
                    ?? AcademicYear::current()?->school_year_id;
            }
            if (! $academicYearId) {
                $academicYearId = AcademicYear::current()?->id;
            }

            $project = P5Project::create([
                'school_year_id' => $schoolYearId,
                'academic_year_id' => $academicYearId,
                'grade_level' => $gradeLevel,
                'phase' => $data['phase'] ?? P5Project::inferPhaseForGrade($gradeLevel),
                'theme' => (string) $data['theme'],
                'title' => trim((string) $data['title']),
                'description' => $data['description'] ?? null,
                'coordinator_id' => $data['coordinator_id'] ?? $actor->id,
                'status' => $data['status'] ?? P5Project::STATUS_ACTIVE,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ]);

            if (! empty($data['dimensions']) && is_array($data['dimensions'])) {
                $this->syncDimensions($project, $data['dimensions']);
            }

            if (! empty($data['facilitators']) && is_array($data['facilitators'])) {
                $this->syncFacilitators($project, $data['facilitators']);
            }

            return $project->load([
                'schoolYear',
                'academicYear',
                'coordinator',
                'dimensions.characterDimension',
                'facilitators.user',
                'facilitators.classroom',
            ]);
        });
    }

    /**
     * Update an existing P5 Project, its mapped dimensions, and facilitators.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProject(P5Project $project, User $actor, array $data): P5Project
    {
        if (! $this->canManageProjects($actor, $project)) {
            abort(403, __('Hanya Koordinator P5 atau Waka Kurikulum yang berwenang mengubah konfigurasi Projek P5 ini.'));
        }

        return DB::transaction(function () use ($project, $data) {
            $updates = [];
            foreach (['school_year_id', 'academic_year_id', 'theme', 'title', 'description', 'coordinator_id', 'status', 'start_date', 'end_date'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updates[$field] = $data[$field];
                }
            }

            if (isset($data['grade_level'])) {
                $gradeLevel = strtoupper(trim((string) $data['grade_level']));
                $updates['grade_level'] = $gradeLevel;
                $updates['phase'] = $data['phase'] ?? P5Project::inferPhaseForGrade($gradeLevel);
            }

            if ($updates !== []) {
                $project->update($updates);
            }

            if (array_key_exists('dimensions', $data) && is_array($data['dimensions'])) {
                $this->syncDimensions($project, $data['dimensions']);
            }

            if (array_key_exists('facilitators', $data) && is_array($data['facilitators'])) {
                $this->syncFacilitators($project, $data['facilitators']);
            }

            return $project->load([
                'schoolYear',
                'academicYear',
                'coordinator',
                'dimensions.characterDimension',
                'facilitators.user',
                'facilitators.classroom',
            ]);
        });
    }

    /**
     * Record bulk or individual facilitator assessments (`MB`, `SB`, `BSH`, `SAB`) and
     * student process notes (`process_notes`) on an active/draft P5 Project.
     *
     * Never fabricates predicates for unsubmitted dimensions (`CONTRIBUTING.md` Rule #1).
     *
     * @param  array<string, mixed>  $payload
     * @return array{saved_assessments: int, saved_notes: int}
     */
    public function recordBulkAssessments(P5Project $project, User $actor, array $payload): array
    {
        if ($project->status === P5Project::STATUS_CLOSED) {
            throw ValidationException::withMessages([
                'status' => __('Projek P5 yang sudah ditutup tidak dapat dinilai kembali.'),
            ]);
        }

        $validDimensionIds = $project->dimensions()->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($validDimensionIds === []) {
            throw ValidationException::withMessages([
                'dimensions' => __('Projek P5 belum memiliki pemetaan dimensi capaian untuk dinilai.'),
            ]);
        }

        $allowedPredicates = array_keys(P5ProjectAssessment::PREDICATES);
        $entries = $payload['assessments'] ?? [];

        if (! is_array($entries) || $entries === []) {
            throw ValidationException::withMessages([
                'assessments' => __('Data penilaian siswa wajib diisi.'),
            ]);
        }

        return DB::transaction(function () use ($project, $actor, $entries, $validDimensionIds, $allowedPredicates) {
            $savedAssessments = 0;
            $savedNotes = 0;

            foreach ($entries as $key => $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $student = $this->resolveStudentFromEntry($entry, $key);
                if (! $student) {
                    throw ValidationException::withMessages([
                        'assessments' => __('Siswa yang dinilai tidak ditemukan.'),
                    ]);
                }

                if (! $this->canAssessStudent($actor, $project, $student)) {
                    abort(403, __('Anda tidak berwenang menilai siswa pada rombel atau Projek P5 ini.'));
                }

                $dimensionScores = $entry['predicates'] ?? $entry['dimensions'] ?? [];
                if (is_array($dimensionScores)) {
                    foreach ($dimensionScores as $dimIdKey => $rawValue) {
                        $dimId = is_array($rawValue) ? (int) ($rawValue['p5_project_dimension_id'] ?? $dimIdKey) : (int) $dimIdKey;
                        $predicateValue = is_array($rawValue) ? ($rawValue['predicate'] ?? null) : $rawValue;

                        if ($predicateValue === null || trim((string) $predicateValue) === '') {
                            continue;
                        }

                        if (! in_array($dimId, $validDimensionIds, true)) {
                            throw ValidationException::withMessages([
                                'assessments' => __('Dimensi projek yang dipilih tidak terdaftar pada Projek P5 ini.'),
                            ]);
                        }

                        $normalized = strtoupper(trim((string) $predicateValue));
                        if (! in_array($normalized, $allowedPredicates, true)) {
                            throw ValidationException::withMessages([
                                'predicate' => __('Predikat P5 harus salah satu dari MB, SB, BSH, atau SAB.'),
                            ]);
                        }

                        P5ProjectAssessment::updateOrCreate(
                            [
                                'p5_project_dimension_id' => $dimId,
                                'student_id' => $student->id,
                            ],
                            [
                                'p5_project_id' => $project->id,
                                'classroom_id' => $student->classroom_id,
                                'assessor_id' => $actor->id,
                                'predicate' => $normalized,
                            ]
                        );
                        $savedAssessments++;
                    }
                }

                if (array_key_exists('process_notes', $entry)) {
                    $noteText = trim((string) ($entry['process_notes'] ?? ''));
                    if ($noteText !== '') {
                        P5ProjectStudentNote::updateOrCreate(
                            [
                                'p5_project_id' => $project->id,
                                'student_id' => $student->id,
                            ],
                            [
                                'assessor_id' => $actor->id,
                                'process_notes' => $noteText,
                            ]
                        );
                        $savedNotes++;
                    }
                }
            }

            return [
                'saved_assessments' => $savedAssessments,
                'saved_notes' => $savedNotes,
            ];
        });
    }

    /**
     * Compile honest Kurikulum Merdeka Rapor Projek P5 for a student (`ALUR_KERJA.md` D4 step 3).
     * Never invents default predicates when a dimension has not yet been assessed (`null`).
     *
     * @return array<string, mixed>
     */
    public function buildStudentReport(Student $student, ?int $schoolYearId = null): array
    {
        $student->loadMissing(['user', 'classroom.teacher']);

        $activeEnrollment = ClassEnrollment::with(['schoolYear', 'classroom.teacher'])
            ->where('student_id', $student->id)
            ->when($schoolYearId !== null, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->orderByDesc('started_on')
            ->orderByDesc('id')
            ->first();

        $resolvedSchoolYear = $schoolYearId
            ? SchoolYear::find($schoolYearId)
            : ($activeEnrollment?->schoolYear ?? SchoolYear::where('status', SchoolYear::STATUS_ACTIVE)->first());

        $classroom = $activeEnrollment?->classroom ?? $student->classroom;
        $gradeLevel = strtoupper(trim((string) ($classroom?->grade_level ?? '')));

        $projects = P5Project::query()
            ->with(['schoolYear', 'academicYear', 'coordinator', 'dimensions.characterDimension'])
            ->where(function ($outer) use ($student, $resolvedSchoolYear, $gradeLevel) {
                $outer->where(function ($matchingCohort) use ($resolvedSchoolYear, $gradeLevel) {
                    $matchingCohort->whereIn('status', [P5Project::STATUS_ACTIVE, P5Project::STATUS_CLOSED]);
                    if ($resolvedSchoolYear) {
                        $matchingCohort->where('school_year_id', $resolvedSchoolYear->id);
                    }
                    if ($gradeLevel !== '') {
                        $matchingCohort->where('grade_level', $gradeLevel);
                    } else {
                        $matchingCohort->whereRaw('1 = 0');
                    }
                })
                    ->orWhereHas('assessments', fn ($aq) => $aq->where('student_id', $student->id))
                    ->orWhereHas('studentNotes', fn ($nq) => $nq->where('student_id', $student->id));
            })
            ->when($schoolYearId !== null, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->orderBy('id')
            ->get();

        $projectIds = $projects->pluck('id')->all();

        $assessmentsByDim = P5ProjectAssessment::query()
            ->with('assessor')
            ->where('student_id', $student->id)
            ->whereIn('p5_project_id', $projectIds)
            ->get()
            ->keyBy('p5_project_dimension_id');

        $notesByProject = P5ProjectStudentNote::query()
            ->with('assessor')
            ->where('student_id', $student->id)
            ->whereIn('p5_project_id', $projectIds)
            ->get()
            ->keyBy('p5_project_id');

        $projectReports = $projects->map(function (P5Project $project) use ($assessmentsByDim, $notesByProject) {
            $dimensionRows = $project->dimensions->map(function (P5ProjectDimension $dim) use ($assessmentsByDim) {
                /** @var P5ProjectAssessment|null $recorded */
                $recorded = $assessmentsByDim->get($dim->id);
                $predicate = $recorded?->predicate;

                return [
                    'project_dimension_id' => $dim->id,
                    'dimension_id' => $dim->character_dimension_id,
                    'dimension_name' => $dim->characterDimension?->name ?? '-',
                    'element' => $dim->element,
                    'sub_element' => $dim->sub_element,
                    'target_achievement' => $dim->target_achievement,
                    'predicate' => $predicate,
                    'predicate_label' => P5ProjectAssessment::predicateLabel($predicate),
                    'assessor_name' => $recorded?->assessor?->name,
                    'assessed_at' => $recorded?->updated_at?->toIso8601String(),
                ];
            })->values();

            /** @var P5ProjectStudentNote|null $note */
            $note = $notesByProject->get($project->id);

            return [
                'uuid' => $project->uuid,
                'title' => $project->title,
                'theme_code' => $project->theme,
                'theme_label' => $project->theme_label,
                'description' => $project->description,
                'grade_level' => $project->grade_level,
                'phase' => $project->phase,
                'status' => $project->status,
                'coordinator_name' => $project->coordinator?->name,
                'dimensions' => $dimensionRows,
                'process_notes' => $note?->process_notes,
                'note_assessor_name' => $note?->assessor?->name,
            ];
        })->values();

        return [
            'student' => $student,
            'classroom' => $classroom,
            'school_year' => $resolvedSchoolYear,
            'phase' => $gradeLevel !== '' ? P5Project::inferPhaseForGrade($gradeLevel) : null,
            'predicates_legend' => P5ProjectAssessment::PREDICATES,
            'projects' => $projectReports,
            'homeroom_teacher_name' => $classroom?->teacher?->name,
            'principal_name' => signature_name(school_principal_name()),
            'school_name' => school_name(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $dimensions
     */
    private function syncDimensions(P5Project $project, array $dimensions): void
    {
        $keptIds = [];
        $order = 1;

        foreach ($dimensions as $dimData) {
            if (! is_array($dimData) || empty($dimData['character_dimension_id']) || empty($dimData['element'])) {
                continue;
            }

            $dim = P5ProjectDimension::updateOrCreate(
                [
                    'p5_project_id' => $project->id,
                    'character_dimension_id' => (int) $dimData['character_dimension_id'],
                    'element' => trim((string) $dimData['element']),
                ],
                [
                    'sub_element' => isset($dimData['sub_element']) && filled($dimData['sub_element'])
                        ? trim((string) $dimData['sub_element'])
                        : null,
                    'target_achievement' => trim((string) ($dimData['target_achievement'] ?? '')),
                    'display_order' => (int) ($dimData['display_order'] ?? $order),
                ]
            );

            $keptIds[] = $dim->id;
            $order++;
        }

        if ($keptIds !== []) {
            P5ProjectDimension::where('p5_project_id', $project->id)
                ->whereNotIn('id', $keptIds)
                ->delete();
        }
    }

    /**
     * @param  array<int, array<string, mixed>|int>  $facilitators
     */
    private function syncFacilitators(P5Project $project, array $facilitators): void
    {
        P5ProjectFacilitator::where('p5_project_id', $project->id)->delete();

        foreach ($facilitators as $item) {
            $userId = is_array($item) ? ($item['user_id'] ?? null) : $item;
            $classroomId = is_array($item) ? ($item['classroom_id'] ?? null) : null;

            if (! $userId) {
                continue;
            }

            P5ProjectFacilitator::firstOrCreate([
                'p5_project_id' => $project->id,
                'user_id' => (int) $userId,
                'classroom_id' => $classroomId ? (int) $classroomId : null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function resolveStudentFromEntry(array $entry, int|string $fallbackKey): ?Student
    {
        if (! empty($entry['student_uuid'])) {
            return Student::with('classroom')->where('uuid', (string) $entry['student_uuid'])->first();
        }

        if (! empty($entry['student_id'])) {
            return Student::with('classroom')->find((int) $entry['student_id']);
        }

        if (is_string($fallbackKey) && strlen($fallbackKey) === 36) {
            return Student::with('classroom')->where('uuid', $fallbackKey)->first();
        }

        if (is_numeric($fallbackKey)) {
            return Student::with('classroom')->find((int) $fallbackKey);
        }

        return null;
    }
}
