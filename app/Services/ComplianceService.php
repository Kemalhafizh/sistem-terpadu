<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\EmployeeAttendance;
use App\Models\EdsPeriod;
use App\Models\EdsStandard;
use App\Models\EdsAssessment;
use App\Models\LessonPlan;
use App\Models\LibraryBook;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TeachingJournal;
use App\Models\User;

class ComplianceService
{
    protected EdsService $edsService;

    public function __construct(EdsService $edsService)
    {
        $this->edsService = $edsService;
    }

    /**
     * Get Teacher to Student ratio.
     * Ideal standard: 1 teacher for 20 students.
     */
    public function getTeacherStudentRatio(): float
    {
        $teachers = User::where('role', 'guru')->count();
        $students = Student::where('status', 'aktif')->count();
        if ($students === 0) {
            $students = Student::count();
        }

        if ($teachers === 0) {
            return 0.0;
        }

        return round($students / $teachers, 2);
    }

    /**
     * Get Book to Student ratio.
     * Ideal standard: 10 books per student.
     */
    public function getBookStudentRatio(): float
    {
        $books = LibraryBook::count(); // Total distinct books
        $students = Student::where('status', 'aktif')->count();
        if ($students === 0) {
            $students = Student::count();
        }

        if ($students === 0) {
            return 0.0;
        }

        return round($books / $students, 2);
    }

    /**
     * Get teaching journal compliance rate.
     * Percentage of approved journals.
     */
    public function getTeachingJournalComplianceRate(): float
    {
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return 0.0;
        }

        $total = TeachingJournal::where('academic_year_id', $activeYear->id)->count();
        if ($total === 0) {
            return 0.0;
        }

        $approved = TeachingJournal::where('academic_year_id', $activeYear->id)
            ->where('status', 'approved')
            ->count();

        return round(($approved / $total) * 100, 2);
    }

    /**
     * Get syllabus upload and completion rate.
     */
    public function getSyllabiCompletionRate(): float
    {
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return 0.0;
        }

        $totalSubjects = Subject::count();
        if ($totalSubjects === 0) {
            return 0.0;
        }

        $approvedSyllabi = Syllabus::where('academic_year_id', $activeYear->id)
            ->where('status', 'approved')
            ->distinct('subject_id')
            ->count();

        return round(($approvedSyllabi / $totalSubjects) * 100, 2);
    }

    /**
     * Get lesson plan completion rate.
     */
    public function getLessonPlanCompletionRate(): float
    {
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return 0.0;
        }

        $total = LessonPlan::where('academic_year_id', $activeYear->id)->count();
        if ($total === 0) {
            return 0.0;
        }

        $approved = LessonPlan::where('academic_year_id', $activeYear->id)
            ->where('status', 'approved')
            ->count();

        return round(($approved / $total) * 100, 2);
    }

    /**
     * Get student attendance rate.
     */
    public function getStudentAttendanceRate(): float
    {
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return 0.0;
        }

        $total = Attendance::where('academic_year_id', $activeYear->id)->count();
        if ($total === 0) {
            return 0.0;
        }

        $present = Attendance::where('academic_year_id', $activeYear->id)
            ->where('status', 'H')
            ->count();

        return round(($present / $total) * 100, 2);
    }

    /**
     * Get teacher attendance rate.
     */
    public function getTeacherAttendanceRate(): float
    {
        $total = EmployeeAttendance::count();
        if ($total === 0) {
            return 0.0;
        }

        $present = EmployeeAttendance::where('status', 'hadir')->count();

        return round(($present / $total) * 100, 2);
    }

    /**
     * Get financial health score (payment rate vs total billing).
     */
    public function getFinancialHealthScore(): float
    {
        $activeYear = AcademicYear::current();
        if (!$activeYear) {
            return 0.0;
        }

        $totalAmount = Billing::where('academic_year_id', $activeYear->id)->sum('amount');
        if ($totalAmount == 0) {
            return 0.0;
        }

        $remaining = Billing::where('academic_year_id', $activeYear->id)->sum('remaining_balance');
        $paid = $totalAmount - $remaining;

        return round(($paid / $totalAmount) * 100, 2);
    }

    /**
     * Get library utilization rate (students borrowing books).
     */
    public function getLibraryUtilizationRate(): float
    {
        $students = Student::where('status', 'aktif')->count();
        if ($students === 0) {
            $students = Student::count();
        }

        if ($students === 0) {
            return 0.0;
        }

        $activeBorrowers = LibraryLoan::distinct('student_id')->count('student_id');

        return round(($activeBorrowers / $students) * 100, 2);
    }

    /**
     * Get latest EDS period scores.
     */
    public function getEdsScores(): array
    {
        $latestPeriod = EdsPeriod::orderBy('created_at', 'desc')->first();
        if (!$latestPeriod) {
            return [
                'overall_average' => 0.0,
                'overall_progress' => 0,
                'standards' => []
            ];
        }

        return $this->edsService->getStandardScores($latestPeriod);
    }

    /**
     * Compile everything into a comprehensive compliance report.
     */
    public function getFullComplianceReport(): array
    {
        $teacherStudentRatio = $this->getTeacherStudentRatio();
        $bookStudentRatio = $this->getBookStudentRatio();
        $teachingJournalRate = $this->getTeachingJournalComplianceRate();
        $syllabiRate = $this->getSyllabiCompletionRate();
        $lessonPlanRate = $this->getLessonPlanCompletionRate();
        $studentAttendance = $this->getStudentAttendanceRate();
        $teacherAttendance = $this->getTeacherAttendanceRate();
        $financeScore = $this->getFinancialHealthScore();
        $libraryRate = $this->getLibraryUtilizationRate();
        $edsScores = $this->getEdsScores();

        // Evaluate teacher-student ratio score (Target: <= 20 students per teacher)
        $ratioScore = $teacherStudentRatio <= 20.0 && $teacherStudentRatio > 0 ? 100.0 : max(0.0, 100.0 - (($teacherStudentRatio - 20.0) * 5));
        if ($teacherStudentRatio == 0) $ratioScore = 0.0;

        // Evaluate book-student ratio score (Target: >= 10 books per student)
        $bookScore = $bookStudentRatio >= 10.0 ? 100.0 : ($bookStudentRatio / 10.0) * 100;

        // Convert EDS overall score to 0-100 scale (EDS score is 1-4)
        $edsReadiness = $edsScores['overall_average'] > 0 ? (($edsScores['overall_average'] - 1) / 3) * 100 : 0.0;

        return [
            'metrics' => [
                'teacher_student_ratio' => [
                    'label' => 'Rasio Guru - Siswa',
                    'value' => '1 : ' . ($teacherStudentRatio > 0 ? $teacherStudentRatio : '-'),
                    'score' => round($ratioScore, 2),
                    'status' => $ratioScore >= 80 ? 'terpenuhi' : ($ratioScore >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Idealnya maksimal 20 siswa per guru untuk interaksi optimal.'
                ],
                'book_student_ratio' => [
                    'label' => 'Rasio Buku - Siswa',
                    'value' => ($bookStudentRatio > 0 ? $bookStudentRatio : '0') . ' Buku/Siswa',
                    'score' => round($bookScore, 2),
                    'status' => $bookScore >= 80 ? 'terpenuhi' : ($bookScore >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Idealnya minimal memiliki 10 judul buku per siswa di perpustakaan.'
                ],
                'teaching_journal_rate' => [
                    'label' => 'Jurnal Mengajar',
                    'value' => $teachingJournalRate . '%',
                    'score' => $teachingJournalRate,
                    'status' => $teachingJournalRate >= 80 ? 'terpenuhi' : ($teachingJournalRate >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Persentase jurnal mengajar guru yang telah disetujui pimpinan.'
                ],
                'syllabi_rate' => [
                    'label' => 'Kelengkapan Silabus',
                    'value' => $syllabiRate . '%',
                    'score' => $syllabiRate,
                    'status' => $syllabiRate >= 80 ? 'terpenuhi' : ($syllabiRate >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Persentase silabus mata pelajaran terunggah dan disetujui.'
                ],
                'lesson_plan_rate' => [
                    'label' => 'Kelengkapan RPP',
                    'value' => $lessonPlanRate . '%',
                    'score' => $lessonPlanRate,
                    'status' => $lessonPlanRate >= 80 ? 'terpenuhi' : ($lessonPlanRate >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Persentase draf RPP yang disetujui dibanding total rencana.'
                ],
                'student_attendance' => [
                    'label' => 'Kehadiran Siswa',
                    'value' => $studentAttendance . '%',
                    'score' => $studentAttendance,
                    'status' => $studentAttendance >= 90 ? 'terpenuhi' : ($studentAttendance >= 75 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Kehadiran siswa secara kumulatif selama periode berjalan.'
                ],
                'teacher_attendance' => [
                    'label' => 'Kehadiran Guru',
                    'value' => $teacherAttendance . '%',
                    'score' => $teacherAttendance,
                    'status' => $teacherAttendance >= 90 ? 'terpenuhi' : ($teacherAttendance >= 75 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Kehadiran staf pendidik secara kumulatif periode berjalan.'
                ],
                'finance_score' => [
                    'label' => 'Kesehatan Finansial',
                    'value' => $financeScore . '%',
                    'score' => $financeScore,
                    'status' => $financeScore >= 80 ? 'terpenuhi' : ($financeScore >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Rasio pelunasan tagihan sekolah / kelancaran iuran SPP siswa.'
                ],
                'library_rate' => [
                    'label' => 'Utilitas Perpustakaan',
                    'value' => $libraryRate . '%',
                    'score' => $libraryRate,
                    'status' => $libraryRate >= 50 ? 'terpenuhi' : ($libraryRate >= 25 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Persentase siswa yang aktif meminjam buku di perpustakaan.'
                ],
                'eds_readiness' => [
                    'label' => 'Evaluasi Diri Sekolah',
                    'value' => ($edsScores['overall_average'] > 0 ? number_format($edsScores['overall_average'], 2) : '0.00') . ' / 4.00',
                    'score' => round($edsReadiness, 2),
                    'status' => $edsReadiness >= 75 ? 'terpenuhi' : ($edsReadiness >= 50 ? 'perlu_perbaikan' : 'belum_terpenuhi'),
                    'desc' => 'Pencapaian evaluasi diri mandiri terhadap 8 standar mutu.'
                ],
            ],
            'overall_score' => $this->getAccreditationReadinessScore(),
        ];
    }

    /**
     * Compute weighted accreditation readiness score.
     */
    public function getAccreditationReadinessScore(): float
    {
        $teacherStudentRatio = $this->getTeacherStudentRatio();
        $bookStudentRatio = $this->getBookStudentRatio();
        $teachingJournalRate = $this->getTeachingJournalComplianceRate();
        $syllabiRate = $this->getSyllabiCompletionRate();
        $lessonPlanRate = $this->getLessonPlanCompletionRate();
        $studentAttendance = $this->getStudentAttendanceRate();
        $teacherAttendance = $this->getTeacherAttendanceRate();
        $financeScore = $this->getFinancialHealthScore();
        $libraryRate = $this->getLibraryUtilizationRate();
        $edsScores = $this->getEdsScores();

        // 1. Ratio scores
        $ratioScore = $teacherStudentRatio <= 20.0 && $teacherStudentRatio > 0 ? 100.0 : max(0.0, 100.0 - (($teacherStudentRatio - 20.0) * 5));
        if ($teacherStudentRatio == 0) $ratioScore = 0.0;

        $bookScore = $bookStudentRatio >= 10.0 ? 100.0 : ($bookStudentRatio / 10.0) * 100;

        // 2. EDS score scaling
        $edsReadiness = $edsScores['overall_average'] > 0 ? (($edsScores['overall_average'] - 1) / 3) * 100 : 0.0;

        // Weights:
        // Teacher-Student: 15%, Book-Student: 10%
        // Journal: 10%, Syllabi: 10%, LessonPlan: 15%
        // Student Attendance: 10%, Teacher Attendance: 10%
        // Finance: 5%, Library: 5%
        // EDS: 10%
        $weighted = ($ratioScore * 0.15) +
                    ($bookScore * 0.10) +
                    ($teachingJournalRate * 0.10) +
                    ($syllabiRate * 0.10) +
                    ($lessonPlanRate * 0.15) +
                    ($studentAttendance * 0.10) +
                    ($teacherAttendance * 0.10) +
                    ($financeScore * 0.05) +
                    ($libraryRate * 0.05) +
                    ($edsReadiness * 0.10);

        return round($weighted, 2);
    }
}
