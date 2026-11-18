<?php

namespace App\Services\Academic;

use App\Models\ClassPlacement;
use App\Models\Classroom;
use App\Models\ElectiveChoice;
use App\Models\ElectiveSurvey;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Layanan Survei Peminatan Mata Pelajaran Pilihan (Kurikulum Merdeka) & Plotting Rombel XI.
 *
 * Prinsip:
 * - Survei hanya memetakan minat siswa kelas X.
 * - Sistem menyajikan statistik faktual dan tingkat kecocokan per rombel sebagai saran.
 * - Sistem TIDAK mengalokasikan siswa secara otomatis; penempatan (`class_placements`)
 *   sepenuhnya dikendalikan Waka Kurikulum.
 */
class ElectiveSurveyService
{
    /**
     * @param  list<int>  $subjectIds
     * @param  array<int, int>|null  $ranks  Map of subject_id => rank (1..N) when survey is ranked
     * @return Collection<int, ElectiveChoice>
     */
    public function submitChoices(
        ElectiveSurvey $survey,
        Student $student,
        array $subjectIds,
        ?array $ranks = null,
    ): Collection {
        if (! $survey->isOpen()) {
            throw ValidationException::withMessages([
                'survey' => [__('Survei peminatan sedang tidak dibuka.')],
                'error_code' => ['SURVEY_CLOSED'],
            ]);
        }

        $uniqueSubjectIds = array_values(array_unique(array_map('intval', $subjectIds)));
        $count = count($uniqueSubjectIds);

        if ($count < $survey->min_choices || $count > $survey->max_choices) {
            throw ValidationException::withMessages([
                'subject_ids' => [__('Jumlah mata pelajaran pilihan harus antara :min sampai :max.', [
                    'min' => $survey->min_choices,
                    'max' => $survey->max_choices,
                ])],
                'error_code' => ['INVALID_CHOICE_COUNT'],
            ]);
        }

        $offeredIds = $survey->subjects()->pluck('subjects.id')->map(fn ($id) => (int) $id)->all();
        $invalidIds = array_diff($uniqueSubjectIds, $offeredIds);
        if ($invalidIds !== []) {
            throw ValidationException::withMessages([
                'subject_ids' => [__('Terdapat mata pelajaran yang tidak ditawarkan pada survei ini.')],
                'error_code' => ['SUBJECT_NOT_OFFERED'],
            ]);
        }

        return DB::transaction(function () use ($survey, $student, $uniqueSubjectIds, $ranks) {
            ElectiveChoice::query()
                ->where('elective_survey_id', $survey->id)
                ->where('student_id', $student->id)
                ->delete();

            $created = collect();
            foreach ($uniqueSubjectIds as $index => $subjectId) {
                $rank = $survey->ranked
                    ? (isset($ranks[$subjectId]) ? (int) $ranks[$subjectId] : ($index + 1))
                    : null;

                $created->push(ElectiveChoice::create([
                    'elective_survey_id' => $survey->id,
                    'student_id' => $student->id,
                    'subject_id' => $subjectId,
                    'rank' => $rank,
                ]));
            }

            return $created;
        });
    }

    /**
     * Compute real statistics from submitted choices without fabricating numbers.
     *
     * @param  Collection<int, Student>|null  $gradeXStudents
     * @return array{
     *     total_submitted_students: int,
     *     subject_counts: list<array{subject_id: int, name: string, code: string|null, count: int}>,
     *     pair_combinations: list<array{subjects: list<string>, subject_ids: list<int>, count: int}>,
     *     by_classroom: list<array{classroom_id: int, classroom_name: string, submitted_count: int, unsubmitted_count: int}>,
     *     unsubmitted_students: list<array{student_id: int, name: string, nis: string|null, classroom_name: string|null}>
     * }
     */
    public function statistics(ElectiveSurvey $survey, ?Collection $gradeXStudents = null): array
    {
        $offeredSubjects = $survey->subjects()->orderBy('name')->get();
        $choices = ElectiveChoice::query()
            ->where('elective_survey_id', $survey->id)
            ->with('subject')
            ->get();

        $byStudent = $choices->groupBy('student_id');
        $submittedStudentIds = $byStudent->keys()->map(fn ($id) => (int) $id)->all();

        // 1. Counts per offered subject
        $countsBySubjectId = $choices->groupBy('subject_id')->map->count();
        $subjectCounts = [];
        foreach ($offeredSubjects as $subject) {
            $subjectCounts[] = [
                'subject_id' => (int) $subject->id,
                'name' => $subject->name,
                'code' => $subject->code,
                'count' => (int) ($countsBySubjectId[$subject->id] ?? 0),
            ];
        }
        usort($subjectCounts, fn (array $a, array $b) => $b['count'] <=> $a['count']);

        // 2. Pair combinations chosen together
        $subjectNames = $offeredSubjects->pluck('name', 'id');
        $pairCounts = [];
        foreach ($byStudent as $studentChoices) {
            $ids = $studentChoices->pluck('subject_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $n = count($ids);
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $key = $ids[$i].':'.$ids[$j];
                    $pairCounts[$key] = ($pairCounts[$key] ?? 0) + 1;
                }
            }
        }
        arsort($pairCounts);
        $pairCombinations = [];
        foreach ($pairCounts as $key => $count) {
            [$id1, $id2] = array_map('intval', explode(':', $key));
            $pairCombinations[] = [
                'subject_ids' => [$id1, $id2],
                'subjects' => [
                    (string) ($subjectNames[$id1] ?? "#{$id1}"),
                    (string) ($subjectNames[$id2] ?? "#{$id2}"),
                ],
                'count' => $count,
            ];
        }

        // 3. Grade X students breakdown & unsubmitted list
        if ($gradeXStudents === null) {
            $gradeXStudents = Student::query()
                ->whereHas('classroom', fn ($q) => $q->whereIn('grade_level', ['X', '10']))
                ->with(['user', 'classroom'])
                ->get();
        }

        $unsubmittedStudents = [];
        $classroomBreakdown = [];

        foreach ($gradeXStudents as $student) {
            $cid = (int) ($student->classroom_id ?? 0);
            $cname = $student->classroom?->name ?? 'Tanpa Rombel';
            if (! isset($classroomBreakdown[$cid])) {
                $classroomBreakdown[$cid] = [
                    'classroom_id' => $cid,
                    'classroom_name' => $cname,
                    'submitted_count' => 0,
                    'unsubmitted_count' => 0,
                ];
            }

            if (in_array((int) $student->id, $submittedStudentIds, true)) {
                $classroomBreakdown[$cid]['submitted_count']++;
            } else {
                $classroomBreakdown[$cid]['unsubmitted_count']++;
                $unsubmittedStudents[] = [
                    'student_id' => (int) $student->id,
                    'name' => $student->user?->name ?? "Siswa #{$student->id}",
                    'nis' => $student->nis,
                    'classroom_name' => $student->classroom?->name,
                ];
            }
        }

        return [
            'total_submitted_students' => count($submittedStudentIds),
            'subject_counts' => $subjectCounts,
            'pair_combinations' => $pairCombinations,
            'by_classroom' => array_values($classroomBreakdown),
            'unsubmitted_students' => $unsubmittedStudents,
        ];
    }

    /**
     * Compute how well a student's elective choices match each candidate Grade XI classroom.
     * If the student has not submitted choices, `matched_count` and `match_percent` are `null` (never 0%).
     *
     * @param  Collection<int, Classroom>  $targetClassrooms
     * @return list<array{
     *     classroom_id: int,
     *     classroom_name: string,
     *     matched_count: int|null,
     *     chosen_count: int,
     *     match_percent: float|null,
     *     label: string
     * }>
     */
    public function matchSuggestionsForStudent(
        ElectiveSurvey $survey,
        Student $student,
        Collection $targetClassrooms,
    ): array {
        $chosenSubjectIds = ElectiveChoice::query()
            ->where('elective_survey_id', $survey->id)
            ->where('student_id', $student->id)
            ->pluck('subject_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $chosenCount = count($chosenSubjectIds);
        $suggestions = [];

        foreach ($targetClassrooms as $classroom) {
            $packageIds = $classroom->electiveSubjects->pluck('id')->map(fn ($id) => (int) $id)->all();

            if ($chosenCount === 0) {
                $suggestions[] = [
                    'classroom_id' => (int) $classroom->id,
                    'classroom_name' => $classroom->name,
                    'matched_count' => null,
                    'chosen_count' => 0,
                    'match_percent' => null,
                    'label' => __('Belum mengisi survei'),
                ];
                continue;
            }

            $matchedCount = count(array_intersect($chosenSubjectIds, $packageIds));
            $percent = round(($matchedCount / $chosenCount) * 100.0, 1);

            $suggestions[] = [
                'classroom_id' => (int) $classroom->id,
                'classroom_name' => $classroom->name,
                'matched_count' => $matchedCount,
                'chosen_count' => $chosenCount,
                'match_percent' => $percent,
                'label' => __(':matched dari :total mapel cocok', [
                    'matched' => $matchedCount,
                    'total' => $chosenCount,
                ]),
            ];
        }

        if ($chosenCount > 0) {
            usort($suggestions, fn (array $a, array $b) => ($b['matched_count'] ?? -1) <=> ($a['matched_count'] ?? -1));
        }

        return $suggestions;
    }

    /**
     * Manual placement of a student into a target Grade XI classroom by Waka Kurikulum.
     */
    public function placeStudent(
        SchoolYear $targetYear,
        Student $student,
        Classroom $targetClassroom,
        User $waka,
        ?string $note = null,
    ): ClassPlacement {
        if (! $waka->hasPermission('kurikulum.tetapkan_kenaikan')) {
            abort(403, __('Hanya Waka Kurikulum yang dapat melakukan plotting rombel siswa.'));
        }

        return ClassPlacement::query()->updateOrCreate(
            [
                'school_year_id' => $targetYear->id,
                'student_id' => $student->id,
            ],
            [
                'classroom_id' => $targetClassroom->id,
                'placed_by' => $waka->id,
                'note' => $note,
            ]
        );
    }
}
