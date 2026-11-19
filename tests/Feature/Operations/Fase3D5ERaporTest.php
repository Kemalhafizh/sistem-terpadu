<?php

use App\Http\Middleware\RoleMiddleware;
use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ClassSession;
use App\Models\DailyAssessment;
use App\Models\DailyAssessmentScore;
use App\Models\Grade;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardPdf;
use App\Models\ReportCardTemplate;
use App\Models\Schedule;
use App\Models\SessionAttendance;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\ERaporService;
use App\Services\ERaporPdfService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Cache::flush();
    Storage::fake('local');
    Notification::fake();

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'start_date' => '2026-07-13',
        'end_date' => '2026-12-18',
        'is_active' => true,
    ]);

    $this->wakaKurikulum = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Waka Kurikulum']);
    $this->homeroom = User::factory()->create(['role' => 'guru', 'name' => 'Wali Kelas X-1']);
    $this->otherTeacher = User::factory()->create(['role' => 'guru', 'name' => 'Guru Kelas Lain']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Kepala Sekolah Aktif']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Rafi Pratama']);
    $this->otherStudentUser = User::factory()->create(['role' => 'student', 'name' => 'Siswa Lain']);
    $this->parentUser = User::factory()->create(['role' => 'parent', 'name' => 'Orang Tua Rafi']);

    $this->classroomX = Classroom::factory()->create([
        'name' => 'X-1',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->homeroom->id,
    ]);

    $this->classroomXI = Classroom::factory()->create([
        'name' => 'XI-3',
        'grade_level' => 'XI',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->otherTeacher->id,
    ]);

    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
        'classroom_id' => $this->classroomX->id,
        'nis' => '260001',
        'nisn' => '0088776655',
        'status' => 'active',
    ]);

    $this->otherStudent = Student::factory()->create([
        'user_id' => $this->otherStudentUser->id,
        'classroom_id' => $this->classroomXI->id,
        'nis' => '250002',
        'nisn' => '0099112233',
        'status' => 'active',
    ]);
});

test('phase inference, semester numbering, and grade X vs XI subject grouping follow Kurikulum Merdeka', function () {
    $service = app(ERaporService::class);

    expect($service->inferPhase('X'))->toBe('E')
        ->and($service->inferPhase('XI'))->toBe('F')
        ->and($service->inferPhase('XII'))->toBe('F')
        ->and($service->inferSemesterNumber('X', 'ganjil'))->toBe(1)
        ->and($service->inferSemesterNumber('X', 'genap'))->toBe(2)
        ->and($service->inferSemesterNumber('XI', 'ganjil'))->toBe(3)
        ->and($service->inferSemesterNumber('XII', 'genap'))->toBe(6);

    $matematika = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK', 'category' => 'umum']);
    $fisika = Subject::factory()->create(['name' => 'Fisika', 'code' => 'FSK', 'category' => 'umum']);
    $sosiologi = Subject::factory()->create(['name' => 'Sosiologi', 'code' => 'SOS', 'category' => 'umum']);
    $jawa = Subject::factory()->create(['name' => 'Bahasa Jawa', 'code' => 'BJW', 'category' => 'muatan_lokal']);
    $fiqih = Subject::factory()->create(['name' => 'Fiqih', 'code' => 'FQH', 'category' => 'ciri_khas']);
    $informatika = Subject::factory()->create(['name' => 'Informatika', 'code' => 'INF', 'category' => 'pilihan']);
    $psb = Subject::factory()->create(['name' => 'Pembiasaan Sikap Baik', 'code' => 'PSB', 'category' => 'ciri_khas']);

    $allSubjects = collect([$matematika, $fisika, $sosiologi, $jawa, $fiqih, $informatika, $psb]);

    // Kelas X memisahkan IPA dan IPS di dalam Kelompok A
    $groupX = $service->groupSubjects($allSubjects, 'X', $this->classroomX);
    expect($groupX['phase'])->toBe('E')
        ->and($groupX['kelompok_a']['umum']->pluck('code')->all())->toContain('MTK', 'INF')
        ->and($groupX['kelompok_a']['ipa']->pluck('code')->all())->toBe(['FSK'])
        ->and($groupX['kelompok_a']['ips']->pluck('code')->all())->toBe(['SOS'])
        ->and($groupX['kelompok_a']['muatan_lokal']->pluck('code')->all())->toBe(['BJW'])
        ->and($groupX['kelompok_b'])->toBeEmpty()
        ->and($groupX['kelompok_c']->pluck('code')->all())->toContain('FQH', 'PSB');

    // Kelas XI memisahkan mapel pilihan pada Kelompok B berdasarkan pivot atau kategori
    $this->classroomXI->electiveSubjects()->attach($informatika->id);
    $groupXI = $service->groupSubjects($allSubjects, 'XI', $this->classroomXI);
    expect($groupXI['phase'])->toBe('F')
        ->and($groupXI['kelompok_b']->pluck('code')->all())->toBe(['INF'])
        ->and($groupXI['kelompok_c']->pluck('code')->all())->toContain('PSB');
});

test('TP capaian draft never outputs FALSE, handles single TP without duplication, and persists via DailyAssessment', function () {
    $subject = Subject::factory()->create(['name' => 'Matematika', 'code' => 'MTK', 'kkm' => 75]);
    $service = app(ERaporService::class);

    // 1. Belum ada TP -> draft null (tidak pernah "FALSE")
    $emptyResult = $service->generateTpCapaian($this->student, $subject, $this->academicYear);
    expect($emptyResult['draft'])->toBeNull()
        ->and($emptyResult['tp_count'])->toBe(0);

    // 2. Guru membuat penilaian harian dengan learning_objective via endpoint resmi
    $this->actingAs($this->homeroom)->post(route('daily-assessments.store'), [
        'subject_id' => $subject->id,
        'classroom_id' => $this->classroomX->id,
        'title' => 'UH 1 Persamaan Kuadrat',
        'type' => 'ulangan_harian',
        'assessment_date' => '2026-09-10',
        'max_score' => 100,
        'kkm' => 75,
        'learning_objective' => 'menyelesaikan persamaan dan fungsi kuadrat',
    ])->assertRedirect();

    $uh1 = DailyAssessment::where('title', 'UH 1 Persamaan Kuadrat')->firstOrFail();
    expect($uh1->learning_objective)->toBe('menyelesaikan persamaan dan fungsi kuadrat');

    DailyAssessmentScore::where('daily_assessment_id', $uh1->id)
        ->where('student_id', $this->student->id)
        ->update(['score' => 92]);

    // Hanya 1 TP dinilai -> tepat 1 klausa tanpa pengulangan
    $singleResult = $service->generateTpCapaian($this->student, $subject, $this->academicYear);
    expect($singleResult['tp_count'])->toBe(1)
        ->and($singleResult['draft'])->toBe('Menunjukkan penguasaan yang sangat baik dalam menyelesaikan persamaan dan fungsi kuadrat.')
        ->and($singleResult['draft'])->not->toContain('perlu bimbingan');

    // 3. Tambah TP kedua dengan skor rendah -> tampilkan terkuat dan terlemah
    Setting::set('rapor_predikat_a_min', '90', 'academic');
    $uh2 = DailyAssessment::factory()->create([
        'subject_id' => $subject->id,
        'classroom_id' => $this->classroomX->id,
        'teacher_id' => $this->homeroom->id,
        'academic_year_id' => $this->academicYear->id,
        'learning_objective' => 'menganalisis sistem pertidaksamaan linear',
    ]);
    DailyAssessmentScore::factory()->create([
        'daily_assessment_id' => $uh2->id,
        'student_id' => $this->student->id,
        'score' => 68,
        'remedial_status' => 'none',
    ]);

    $multiResult = $service->generateTpCapaian($this->student, $subject, $this->academicYear);
    expect($multiResult['tp_count'])->toBe(2)
        ->and($multiResult['strongest_tp'])->toBe('menyelesaikan persamaan dan fungsi kuadrat')
        ->and($multiResult['weakest_tp'])->toBe('menganalisis sistem pertidaksamaan linear')
        ->and($multiResult['draft'])->toContain('Menunjukkan penguasaan yang sangat baik dalam menyelesaikan persamaan dan fungsi kuadrat')
        ->and($multiResult['draft'])->toContain('perlu bimbingan dalam menganalisis sistem pertidaksamaan linear');
});

test('day-level attendance counts C3 leave, full-day alpha, daily I, and ignores partial absences in rapor alpa', function () {
    $subject = Subject::factory()->create(['name' => 'Biologi', 'code' => 'BIO']);
    $schedule1 = Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subject->id,
        'teacher_id' => $this->homeroom->id,
        'day' => 'Senin',
        'session_start' => 1,
        'session_end' => 1,
    ]);
    $schedule2 = Schedule::create([
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subject->id,
        'teacher_id' => $this->homeroom->id,
        'day' => 'Senin',
        'session_start' => 2,
        'session_end' => 2,
    ]);

    // Hari 1 (2026-10-05): Hadir jam ke-1, Alpha jam ke-2 -> terhitung 1 hadir + 1 partial absence day (BUKAN Alpa)
    $s1 = ClassSession::create([
        'schedule_id' => $schedule1->id,
        'teacher_id' => $this->homeroom->id,
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'session_date' => '2026-10-05',
        'jam_ke' => 1,
        'scheduled_start' => '07:30:00',
        'scheduled_end' => '08:15:00',
        'status' => ClassSession::STATUS_COMPLETED,
    ]);
    $s2 = ClassSession::create([
        'schedule_id' => $schedule2->id,
        'teacher_id' => $this->homeroom->id,
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'session_date' => '2026-10-05',
        'jam_ke' => 2,
        'scheduled_start' => '08:15:00',
        'scheduled_end' => '09:00:00',
        'status' => ClassSession::STATUS_COMPLETED,
    ]);
    SessionAttendance::create(['class_session_id' => $s1->id, 'student_id' => $this->student->id, 'status' => SessionAttendance::STATUS_HADIR]);
    SessionAttendance::create(['class_session_id' => $s2->id, 'student_id' => $this->student->id, 'status' => SessionAttendance::STATUS_ALPHA]);

    // Hari 2 (2026-10-06): Semua sesi Alpha tanpa izin -> 1 Alpa
    $s3 = ClassSession::create([
        'teacher_id' => $this->homeroom->id,
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'session_date' => '2026-10-06',
        'jam_ke' => 1,
        'scheduled_start' => '07:30:00',
        'scheduled_end' => '08:15:00',
        'status' => ClassSession::STATUS_COMPLETED,
    ]);
    SessionAttendance::create(['class_session_id' => $s3->id, 'student_id' => $this->student->id, 'status' => SessionAttendance::STATUS_ALPHA]);

    // Hari 3-4 (2026-10-07..08): Cuti sakit disetujui C3 (2 hari)
    StudentLeaveRequest::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroomX->id,
        'academic_year_id' => $this->academicYear->id,
        'submitted_by' => $this->parentUser->id,
        'type' => StudentLeaveRequest::TYPE_SAKIT,
        'start_date' => '2026-10-07',
        'end_date' => '2026-10-08',
        'days_count' => 2,
        'reason' => 'Tipes',
        'status' => StudentLeaveRequest::STATUS_APPROVED,
        'decided_by' => $this->homeroom->id,
        'decided_at' => now(),
    ]);

    // Hari 5 (2026-10-09): Presensi harian Izin (I)
    Attendance::create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'date' => '2026-10-09',
        'status' => 'I',
        'recorded_by' => $this->homeroom->id,
    ]);

    $summary = app(ERaporService::class)->calculateDayLevelAttendance($this->student, $this->academicYear);

    expect($summary['hadir'])->toBe(1)
        ->and($summary['sakit'])->toBe(2)
        ->and($summary['izin'])->toBe(1)
        ->and($summary['alpa'])->toBe(1)
        ->and($summary['partial_absence_days'])->toBe(1)
        ->and($summary['partial_absence_dates'])->toBe(['2026-10-05']);
});

test('completeness endpoint, CSV export with historical principal and blank missing grades, bulk NISN PDF/ZIP upload, publish, and signed mobile download work securely', function () {
    $subjectScored = Subject::factory()->create(['name' => 'Kimia', 'code' => 'KMA', 'category' => 'umum']);
    $subjectMissing = Subject::factory()->create(['name' => 'Ekonomi', 'code' => 'EKO', 'category' => 'umum']);

    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $subjectScored->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'pas',
        'score' => 88,
        'recorded_by' => $this->homeroom->id,
    ]);

    // Buat second student di X-1 tanpa nilai Ekonomi supaya teruji nilai kosong ""
    $student2User = User::factory()->create(['role' => 'student', 'name' => 'Zakiya Putri']);
    Student::factory()->create([
        'user_id' => $student2User->id,
        'classroom_id' => $this->classroomX->id,
        'nis' => '260002',
        'nisn' => '0088776699',
    ]);

    $this->classroomX->electiveSubjects()->attach([$subjectScored->id, $subjectMissing->id]);

    // Pejabat historis Kepala Sekolah pada tanggal penerbitan rapor
    $pastKepsek = User::factory()->create(['role' => 'guru', 'name' => 'H. Ahmad Shodiq, M.Pd.']);
    $kepsekPos = Position::firstOrCreate(
        ['code' => 'kepala_sekolah'],
        ['name' => 'Kepala Sekolah', 'is_system' => true, 'is_active' => true]
    );
    PositionAssignment::create([
        'position_id' => $kepsekPos->id,
        'user_id' => $pastKepsek->id,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-11-30',
        'is_acting' => false,
    ]);

    AcademicEvent::create([
        'title' => 'Batas Input Nilai PAS Ganjil',
        'event_type' => 'other',
        'milestone' => 'batas_input_nilai',
        'start_date' => now()->addDays(5),
        'academic_year_id' => $this->academicYear->id,
        'created_by' => $this->wakaKurikulum->id,
        'is_active' => true,
    ]);

    $template = ReportCardTemplate::create([
        'name' => 'Rapor PAS Ganjil 2026/2027',
        'academic_year_id' => $this->academicYear->id,
        'semester' => 'ganjil',
        'semester_number' => 1,
        'phase' => 'E',
        'report_type' => ReportCardTemplate::REPORT_TYPE_PAS,
        'grade_level' => 'X',
        'issued_date' => '2026-10-15',
        'created_by' => $this->wakaKurikulum->id,
    ]);

    // 1. Kelengkapan & Anti-IDOR wali kelas lain
    $this->actingAs($this->homeroom)
        ->getJson(route('report-cards.completeness', ['classroom_id' => $this->classroomX->id]))
        ->assertOk()
        ->assertJsonPath('data.total_students', 2)
        ->assertJsonPath('data.deadline.title', 'Batas Input Nilai PAS Ganjil');

    $this->actingAs($this->otherTeacher)
        ->getJson(route('report-cards.completeness', ['classroom_id' => $this->classroomX->id]))
        ->assertForbidden();

    // 2. Ekspor CSV e-Rapor: kosong untuk mapel tanpa nilai (bukan 0) + resolve pejabat historis
    $this->actingAs($this->otherTeacher)
        ->get(route('report-cards.export-erapor', ['classroom_id' => $this->classroomX->id]))
        ->assertForbidden();

    $csvResponse = $this->actingAs($this->homeroom)->get(route('report-cards.export-erapor', [
        'classroom_id' => $this->classroomX->id,
        'subject_id' => $subjectMissing->id,
        'template_id' => $template->id,
    ]));
    $csvResponse->assertOk();
    $csvContent = $csvResponse->streamedContent();
    expect(str_starts_with($csvContent, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($csvContent)->toContain('H. Ahmad Shodiq, M.Pd.');

    // Parse CSV lines to verify Nilai Akhir column (index 9) is "" and NOT "0"
    $lines = array_map('str_getcsv', array_filter(explode("\n", str_replace("\xEF\xBB\xBF", '', trim($csvContent)))));
    expect($lines[1][9])->toBe('');
    expect($template->fresh()->status)->toBe(ReportCardTemplate::STATUS_DIEKSPOR);

    // 3. Unggah massal PDF resmi: 1 PDF langsung + 1 ZIP berisi PDF cocok & PDF tak cocok
    $pdfDirect = UploadedFile::fake()->createWithContent('0088776655_Rafi_Pratama.pdf', '%PDF-1.4 official-rafi');

    $zipPath = tempnam(sys_get_temp_dir(), 'erapor_zip_');
    $zip = new ZipArchive;
    expect($zip->open($zipPath, ZipArchive::OVERWRITE | ZipArchive::CREATE))->toBeTrue();
    $zip->addFromString('rapor_0088776699_Zakiya.pdf', '%PDF-1.4 official-zakiya');
    $zip->addFromString('9999999999_unknown.pdf', '%PDF-1.4 unknown');
    $zip->close();
    $zipUpload = new UploadedFile($zipPath, 'batch-x1.zip', 'application/zip', null, true);

    $uploadResp = $this->actingAs($this->wakaKurikulum)->postJson(route('report-cards.upload-official-pdfs'), [
        'template_id' => $template->id,
        'classroom_id' => $this->classroomX->id,
        'files' => [$pdfDirect, $zipUpload],
    ])->assertOk();

    @unlink($zipPath);

    expect(count($uploadResp->json('data.matched')))->toBe(2)
        ->and($uploadResp->json('data.unmatched'))->toBe(['9999999999_unknown.pdf'])
        ->and($template->fresh()->status)->toBe(ReportCardTemplate::STATUS_RAPOR_RESMI_DIUNGGAH);

    $rafiPdf = ReportCardPdf::where('report_card_id', $template->id)
        ->where('student_id', $this->studentUser->id)
        ->firstOrFail();
    expect($rafiPdf->is_official)->toBeTrue();
    Storage::disk('local')->assertExists($rafiPdf->file_path);

    // generateSinglePdf tidak boleh menimpa PDF resmi e-Rapor
    $preserved = app(ERaporPdfService::class)->generateSinglePdf($template->id, $this->studentUser->id, $this->wakaKurikulum->id);
    expect($preserved->id)->toBe($rafiPdf->id)
        ->and($preserved->is_official)->toBeTrue()
        ->and($preserved->sha256_hash)->toBe($rafiPdf->sha256_hash);

    // 4. Penerbitan massal per angkatan oleh Waka Kurikulum
    $this->actingAs($this->homeroom)->post(route('report-cards.publish-batch'), [
        'grade_level' => 'X',
        'report_type' => 'pas',
        'semester_number' => 1,
    ])->assertForbidden();

    $this->actingAs($this->wakaKurikulum)->post(route('report-cards.publish-batch'), [
        'academic_year_id' => $this->academicYear->id,
        'grade_level' => 'X',
        'report_type' => 'pas',
        'semester_number' => 1,
        'issued_date' => '2026-10-15',
    ])->assertRedirect();

    expect($template->fresh()->is_published)->toBeTrue()
        ->and($template->fresh()->status)->toBe(ReportCardTemplate::STATUS_TERBIT);

    // 5. Unduhan Web (anti-IDOR) dan Signed URL Mobile API
    $this->actingAs($this->otherStudentUser)
        ->get(route('report-cards.files.show', $rafiPdf->id))
        ->assertForbidden();

    $this->actingAs($this->studentUser)
        ->get(route('report-cards.files.show', $rafiPdf->id))
        ->assertOk();

    Sanctum::actingAs($this->studentUser);
    $mobileList = $this->getJson('/api/v1/documents/reports')->assertOk();
    $signedUrl = $mobileList->json('data.0.file_url');
    expect($mobileList->json('data.0.is_official'))->toBeTrue()
        ->and($signedUrl)->not->toBeEmpty();

    // Signed URL dapat diunduh; tanda tangan yang diubah wajib ditolak 403
    $this->get($signedUrl)->assertOk();
    $tamperedUrl = URL::temporarySignedRoute('api.v1.rapor.files.show', now()->addHour(), ['pdf' => $rafiPdf->id]).'tamper';
    $this->get($tamperedUrl)->assertForbidden();

    // 6. Pratinjau PDF oleh wali kelas membawa watermark PRATINJAU - BUKAN RAPOR RESMI
    ReportCard::create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroomX->id,
        'total_score' => 88,
        'average_score' => 88,
        'rank_in_class' => 1,
    ]);
    $previewResp = $this->actingAs($this->homeroom)->get(route('report-cards.pdf', [
        'student' => $this->student->id,
        'report_type' => 'pts',
    ]));
    $previewResp->assertOk();
    expect(str_starts_with($previewResp->getContent(), '%PDF-'))->toBeTrue();
});
