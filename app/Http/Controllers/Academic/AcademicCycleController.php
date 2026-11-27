<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ElectiveSurvey;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\MeetingNote;
use App\Models\PromotionBatch;
use App\Models\PromotionDecision;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Services\Academic\ElectiveSurveyService;
use App\Services\Academic\EnrollmentService;
use App\Services\Academic\GraduationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicCycleController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollmentService,
        private readonly ElectiveSurveyService $surveyService,
        private readonly GraduationService $graduationService,
    ) {}

    public function index(): View
    {
        $schoolYears = SchoolYear::query()
            ->with(['calendar.events', 'semesters'])
            ->orderByDesc('name')
            ->get();

        $surveys = ElectiveSurvey::query()
            ->with(['schoolYear', 'subjects'])
            ->latest('id')
            ->get();

        $activeSurvey = $surveys->first();
        $surveyStats = $activeSurvey ? $this->surveyService->statistics($activeSurvey) : null;

        $promotionBatches = PromotionBatch::query()
            ->with(['fromSchoolYear', 'toSchoolYear'])
            ->latest('id')
            ->get();

        $graduationBatches = GraduationBatch::query()
            ->with(['schoolYear', 'meetingNote', 'decisions.student.user', 'decisions.classroom'])
            ->latest('id')
            ->get();

        $finalizedMeetingNotes = MeetingNote::query()
            ->where('status', 'finalized')
            ->orderByDesc('meeting_date')
            ->get();

        $subjects = Subject::orderBy('name')->get();
        $milestones = AcademicCalendar::MILESTONES;

        return view('academic.cycle.index', compact(
            'schoolYears',
            'surveys',
            'activeSurvey',
            'surveyStats',
            'promotionBatches',
            'graduationBatches',
            'finalizedMeetingNotes',
            'subjects',
            'milestones',
        ));
    }

    public function storeSchoolYear(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:32', Rule::unique('school_years', 'name')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'copy_from_school_year_id' => ['nullable', 'exists:school_years,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $schoolYear = SchoolYear::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => SchoolYear::STATUS_DRAFT,
            ]);

            $firstSemesterId = null;
            foreach (['Ganjil', 'Genap'] as $semester) {
                $academicYear = AcademicYear::firstOrCreate(
                    [
                        'year' => $schoolYear->name,
                        'semester' => $semester,
                    ],
                    [
                        'school_year_id' => $schoolYear->id,
                        'is_active' => false,
                        'start_date' => $schoolYear->start_date,
                        'end_date' => $schoolYear->end_date,
                    ]
                );
                $firstSemesterId ??= $academicYear->id;
            }

            $copiedFrom = isset($validated['copy_from_school_year_id'])
                ? SchoolYear::with('calendar.events')->find($validated['copy_from_school_year_id'])
                : null;

            $calendar = AcademicCalendar::create([
                'school_year_id' => $schoolYear->id,
                'status' => AcademicCalendar::STATUS_DRAFT,
                'needs_review' => $copiedFrom?->calendar !== null,
            ]);

            if ($copiedFrom?->calendar) {
                foreach ($copiedFrom->calendar->events as $event) {
                    $calendar->events()->create([
                        'title' => $event->title,
                        'description' => $event->description,
                        'event_type' => $event->event_type,
                        'milestone' => $event->milestone,
                        'academic_year_id' => $firstSemesterId ?? $event->academic_year_id,
                        'start_date' => $event->start_date?->addYear(),
                        'end_date' => $event->end_date?->addYear(),
                        'is_all_day' => $event->is_all_day,
                        'color' => $event->color,
                        'target_role' => $event->target_role,
                        'created_by' => auth()->id(),
                        'is_active' => false,
                        'send_notification' => false,
                    ]);
                }
            }
        });

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Tahun ajaran baru beserta draf kalender akademik berhasil dibuat.'));
    }

    public function approveCalendar(Request $request, AcademicCalendar $calendar): RedirectResponse
    {
        if (! $request->user()->hasPermission('pimpinan.mengesahkan')) {
            abort(403, __('Hanya Kepala Sekolah yang berwenang mengesahkan kalender akademik.'));
        }

        if ($calendar->needs_review) {
            return redirect()->route('academic.cycle.index')
                ->withErrors(['calendar' => __('Kalender salinan harus ditinjau ulang tanggalnya oleh Waka Kurikulum sebelum disahkan.')]);
        }

        DB::transaction(function () use ($calendar, $request) {
            $calendar->update([
                'status' => AcademicCalendar::STATUS_PUBLISHED,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'published_at' => now(),
            ]);

            $calendar->events()->update(['is_active' => true]);
        });

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Kalender akademik berhasil disahkan dan diterbitkan.'));
    }

    public function storeSurvey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_year_id' => ['required', 'exists:school_years,id'],
            'title' => ['required', 'string', 'max:180'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'min_choices' => ['required', 'integer', 'min:1', 'max:10'],
            'max_choices' => ['required', 'integer', 'gte:min_choices', 'max:10'],
            'ranked' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in([
                ElectiveSurvey::STATUS_DRAFT,
                ElectiveSurvey::STATUS_OPEN,
                ElectiveSurvey::STATUS_CLOSED,
            ])],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $survey = ElectiveSurvey::create([
                'school_year_id' => $validated['school_year_id'],
                'title' => $validated['title'],
                'opens_at' => $validated['opens_at'] ?? null,
                'closes_at' => $validated['closes_at'] ?? null,
                'min_choices' => $validated['min_choices'],
                'max_choices' => $validated['max_choices'],
                'ranked' => (bool) ($validated['ranked'] ?? false),
                'status' => $validated['status'],
            ]);

            $survey->subjects()->sync($validated['subject_ids']);
        });

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Survei peminatan berhasil disimpan.'));
    }

    public function configureClassroomElectives(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'subject_ids' => ['required', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ]);

        $classroom->electiveSubjects()->sync($validated['subject_ids']);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Susunan mata pelajaran pilihan untuk rombel :name berhasil disimpan.', [
                'name' => $classroom->name,
            ]));
    }

    public function placeStudents(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_year_id' => ['required', 'exists:school_years,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $targetYear = SchoolYear::findOrFail($validated['school_year_id']);
        $targetClassroom = Classroom::findOrFail($validated['classroom_id']);
        $students = Student::whereIn('id', $validated['student_ids'])->get();

        DB::transaction(function () use ($students, $targetYear, $targetClassroom, $request, $validated) {
            foreach ($students as $student) {
                $this->surveyService->placeStudent(
                    $targetYear,
                    $student,
                    $targetClassroom,
                    $request->user(),
                    $validated['note'] ?? null
                );
            }
        });

        return redirect()->route('academic.cycle.index')
            ->with('success', __(':count siswa berhasil diplot ke rombel :classroom.', [
                'count' => $students->count(),
                'classroom' => $targetClassroom->name,
            ]));
    }

    public function announceSurvey(Request $request, ElectiveSurvey $survey): RedirectResponse
    {
        $survey->update([
            'status' => ElectiveSurvey::STATUS_COMPLETED,
            'announced_at' => now(),
        ]);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Hasil plotting rombel XI telah diumumkan ke siswa dan orang tua.'));
    }

    public function createPromotionBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_school_year_id' => ['required', 'exists:school_years,id'],
            'to_school_year_id' => ['required', 'exists:school_years,id', 'different:from_school_year_id'],
        ]);

        $fromYear = SchoolYear::findOrFail($validated['from_school_year_id']);
        $toYear = SchoolYear::findOrFail($validated['to_school_year_id']);

        $this->enrollmentService->createPromotionBatch($fromYear, $toYear, $request->user());

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Draf batch kenaikan kelas berhasil disiapkan.'));
    }

    public function reviewDecision(Request $request, PromotionDecision $decision): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['naik', 'tinggal', 'lulus'])],
            'homeroom_note' => ['nullable', 'string', 'max:1000'],
            'to_classroom_id' => ['nullable', 'exists:classrooms,id'],
        ]);

        $this->enrollmentService->reviewByHomeroom(
            $decision,
            $request->user(),
            $validated['decision'],
            $validated['homeroom_note'] ?? null,
            isset($validated['to_classroom_id']) ? (int) $validated['to_classroom_id'] : null,
        );

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Keputusan kenaikan siswa berhasil diperbarui.'));
    }

    public function decideBatch(Request $request, PromotionBatch $batch): RedirectResponse
    {
        $this->enrollmentService->decideBatch($batch, $request->user());

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Batch kenaikan kelas berhasil ditetapkan oleh Waka Kurikulum.'));
    }

    public function holdBatch(Request $request, PromotionBatch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->enrollmentService->holdBatch($batch, $request->user(), $validated['reason']);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Penetapan kenaikan kelas ditahan oleh Kepala Sekolah untuk ditinjau kembali.'));
    }

    public function executeBatch(Request $request, PromotionBatch $batch): RedirectResponse
    {
        $this->enrollmentService->executeBatch($batch, $request->user());

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Batch kenaikan kelas berhasil dieksekusi ke riwayat rombel.'));
    }

    public function createGraduationBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_year_id' => ['required', 'exists:school_years,id'],
            'graduation_year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'graduation_date' => ['nullable', 'date'],
            'sk_number' => ['nullable', 'string', 'max:120'],
            'sk_date' => ['nullable', 'date'],
            'meeting_note_id' => ['nullable', 'exists:meeting_notes,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $schoolYear = SchoolYear::findOrFail($validated['school_year_id']);
        $this->graduationService->createOrSyncBatch($schoolYear, $request->user(), $validated);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Draf batch kelulusan kelas XII berhasil disiapkan.'));
    }

    public function reviewGraduationDecision(Request $request, GraduationDecision $graduationDecision): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in([
                GraduationDecision::DECISION_GRADUATED,
                GraduationDecision::DECISION_NOT_GRADUATED,
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->graduationService->updateDecision(
            $graduationDecision,
            $request->user(),
            $validated['decision'],
            $validated['note'] ?? null
        );

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Keputusan kelulusan siswa berhasil diperbarui.'));
    }

    public function bulkGraduationDecisions(Request $request, GraduationBatch $graduationBatch): RedirectResponse
    {
        $validated = $request->validate([
            'decisions' => ['required', 'array', 'min:1'],
        ]);

        $this->graduationService->bulkUpdateDecisions($graduationBatch, $request->user(), $validated['decisions']);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Keputusan kelulusan massal angkatan berhasil disimpan.'));
    }

    public function submitGraduationBatch(Request $request, GraduationBatch $graduationBatch): RedirectResponse
    {
        $validated = $request->validate([
            'meeting_note_id' => ['nullable', 'exists:meeting_notes,id'],
            'sk_number' => ['nullable', 'string', 'max:120'],
            'sk_date' => ['nullable', 'date'],
            'graduation_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->graduationService->submitForApproval($graduationBatch, $request->user(), $validated);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Draf kelulusan kelas XII berhasil diajukan ke Kepala Sekolah untuk pengesahan SK.'));
    }

    public function approveGraduationBatch(Request $request, GraduationBatch $graduationBatch): RedirectResponse
    {
        $validated = $request->validate([
            'sk_number' => ['nullable', 'string', 'max:120'],
            'sk_date' => ['nullable', 'date'],
            'graduation_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->graduationService->approveBatch($graduationBatch, $request->user(), $validated);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('SK kelulusan angkatan kelas XII berhasil disahkan oleh Kepala Sekolah.'));
    }

    public function executeGraduationBatch(Request $request, GraduationBatch $graduationBatch): RedirectResponse
    {
        $validated = $request->validate([
            'sk_number' => ['nullable', 'string', 'max:120'],
            'sk_date' => ['nullable', 'date'],
            'graduation_date' => ['nullable', 'date'],
        ]);

        $this->graduationService->executeAndAnnounce($graduationBatch, $request->user(), $validated);

        return redirect()->route('academic.cycle.index')
            ->with('success', __('Kelulusan kelas XII berhasil diumumkan, riwayat rombel ditutup, dan data alumni telah dibuat.'));
    }
}
