<?php

use App\Models\AcademicYear;
use App\Models\Achievement;
use App\Models\AlumniData;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLoan;
use App\Models\Attendance;
use App\Models\BookableResource;
use App\Models\BudgetPlan;
use App\Models\ClassEnrollment;
use App\Models\Classroom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularGrade;
use App\Models\ExtracurricularMember;
use App\Models\Grade;
use App\Models\IncomingLetter;
use App\Models\LessonPlan;
use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\LetterTemplate;
use App\Models\PettyCashFund;
use App\Models\PettyCashTransaction;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\ProfileChangeRequest;
use App\Models\ReportCard;
use App\Models\ResourceBooking;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentLeaveRequest;
use App\Models\StudentMutation;
use App\Models\Subject;
use App\Models\Syllabus;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Notification::fake();

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2026/2027',
        'semester' => 'Ganjil',
        'is_active' => true,
    ]);

    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Kepala Sekolah Sulaone']);
    $this->wakaKurikulum = User::factory()->create(['role' => 'waka_kurikulum', 'name' => 'Waka Kurikulum']);
    $this->wakaSarpras = User::factory()->create(['role' => 'waka_sarpras', 'name' => 'Waka Sarpras']);
    $this->tu = User::factory()->create(['role' => 'staf_tu', 'name' => 'Staf Tata Usaha']);
    $this->waliX1 = User::factory()->create(['role' => 'wali_kelas', 'name' => 'Wali Kelas X-1']);
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Guru Biasa']);
    $this->parentUser = User::factory()->create(['role' => 'parent', 'name' => 'Ibu Wali Murid']);
    $this->otherParent = User::factory()->create(['role' => 'parent', 'name' => 'Wali Murid Lain']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Naufal Maulana']);
    $this->otherStudentUser = User::factory()->create(['role' => 'student', 'name' => 'Siswa Sebelah']);

    $this->classroomX1 = Classroom::factory()->create([
        'name' => 'X-1',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->waliX1->id,
    ]);
    $this->classroomX2 = Classroom::factory()->create([
        'name' => 'X-2',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'teacher_id' => $this->guru->id,
    ]);

    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'parent_id' => $this->parentUser->id,
        'classroom_id' => $this->classroomX1->id,
        'nis' => '261001',
        'nisn' => '0055443322',
        'nik' => '3374020202090005',
        'gender' => 'L',
        'birth_place' => 'Semarang',
        'birth_date' => '2010-03-12',
        'mother_name' => 'Siti Aminah',
        'address' => 'Jl. Pandanaran No. 12 Semarang',
        'status' => 'active',
    ]);

    $this->otherStudent = Student::factory()->create([
        'user_id' => $this->otherStudentUser->id,
        'classroom_id' => $this->classroomX2->id,
        'nis' => '262002',
        'nisn' => '0066554433',
    ]);
});

test('Priority 6 unified approval inbox aggregates cross-module pending items per role, excludes self-requests, respects PositionAssignment, and blocks non-staff', function () {
    // 1. WorkflowRequest: Surat keluar diajukan Staf TU untuk Kepala Sekolah
    WorkflowDefinition::firstOrCreate(
        ['code' => 'letter_approval'],
        [
            'name' => 'Persetujuan Surat Resmi',
            'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]],
            'is_active' => true,
        ]
    );
    $template = LetterTemplate::factory()->create(['name' => 'Surat Tugas', 'code' => 'ST']);
    $letter = Letter::factory()->create([
        'template_id' => $template->id,
        'student_id' => $this->student->id,
        'created_by' => $this->tu->id,
        'status' => Letter::STATUS_DRAFT,
    ]);
    app(WorkflowService::class)->initiate('letter_approval', $this->tu, $letter, 'Mohon persetujuan');

    // 2. Disposisi Surat Masuk untuk Kepsek (dan satu yang tidak untuk Kepsek)
    $incoming = IncomingLetter::create([
        'agenda_number' => '001/SM/2026',
        'reference_number' => 'REF-01',
        'sender' => 'YBWSA',
        'subject' => 'Rapat Yayasan Bulan November',
        'letter_date' => '2026-10-10',
        'received_date' => '2026-10-10',
        'status' => IncomingLetter::STATUS_DISPOSED,
        'created_by' => $this->tu->id,
    ]);
    LetterDisposition::create([
        'incoming_letter_id' => $incoming->id,
        'from_user_id' => $this->tu->id,
        'to_user_id' => $this->kepsek->id,
        'instruction' => 'Mohon verifikasi kehadiran jajaran pimpinan.',
        'priority' => 'segera',
        'status' => LetterDisposition::STATUS_ASSIGNED,
    ]);

    // 3. Izin siswa di X-1 (wali kelas X-1 melihatnya) dan X-2 (wali kelas X-1 tidak melihatnya)
    StudentLeaveRequest::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroomX1->id,
        'academic_year_id' => $this->academicYear->id,
        'submitted_by' => $this->parentUser->id,
        'type' => StudentLeaveRequest::TYPE_IZIN,
        'start_date' => '2026-10-20',
        'end_date' => '2026-10-21',
        'days_count' => 2,
        'reason' => 'Acara keluarga luar kota',
        'status' => StudentLeaveRequest::STATUS_PENDING,
    ]);
    StudentLeaveRequest::create([
        'student_id' => $this->otherStudent->id,
        'classroom_id' => $this->classroomX2->id,
        'academic_year_id' => $this->academicYear->id,
        'submitted_by' => $this->otherParent->id,
        'type' => StudentLeaveRequest::TYPE_IZIN,
        'start_date' => '2026-10-20',
        'end_date' => '2026-10-20',
        'days_count' => 1,
        'reason' => 'Izin siswa X-2',
        'status' => StudentLeaveRequest::STATUS_PENDING,
    ]);

    // 4. Perubahan Data Diri (K4) untuk TU
    ProfileChangeRequest::create([
        'requester_user_id' => $this->studentUser->id,
        'target_user_id' => $this->studentUser->id,
        'target_type' => ProfileChangeRequest::TARGET_STUDENT,
        'target_id' => $this->student->id,
        'field_name' => 'mother_name',
        'field_label' => 'Nama Ibu Kandung',
        'old_value' => 'Siti Aminah',
        'new_value' => 'Hj. Siti Aminah',
        'status' => ProfileChangeRequest::STATUS_PENDING,
    ]);

    // 5. Akademik: LessonPlan (submitted), Syllabus (draft), TeachingJournal (submitted)
    $subject = Subject::factory()->create(['name' => 'Sejarah Indonesia', 'code' => 'SEJ']);
    LessonPlan::create([
        'user_id' => $this->guru->id,
        'subject_id' => $subject->id,
        'classroom_id' => $this->classroomX2->id,
        'academic_year_id' => $this->academicYear->id,
        'topic' => 'Modul Ajar Pergerakan Nasional',
        'learning_objectives' => 'Memahami peran organisasi pergerakan',
        'learning_activities' => 'Diskusi kelompok',
        'status' => 'submitted',
    ]);
    Syllabus::create([
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'grade_level' => 'X',
        'semester' => 'ganjil',
        'uploaded_by' => $this->guru->id,
        'file_path' => 'syllabi/sejarah-x.pdf',
        'status' => 'draft',
    ]);
    TeachingJournal::create([
        'user_id' => $this->guru->id,
        'subject_id' => $subject->id,
        'classroom_id' => $this->classroomX2->id,
        'academic_year_id' => $this->academicYear->id,
        'teaching_date' => '2026-10-19',
        'topic' => 'Sumpah Pemuda 1928',
        'status' => 'submitted',
    ]);

    // 6. Sarpras: AssetLoan (pending) & ResourceBooking (pending)
    $assetCat = AssetCategory::create(['name' => 'Multimedia', 'code' => 'MM']);
    $room = Room::create(['name' => 'Lab Komputer', 'location' => 'Lt. 2']);
    $asset = Asset::create([
        'asset_code' => 'AST-001',
        'name' => 'Proyektor Epson EB-X500',
        'category_id' => $assetCat->id,
        'room_id' => $room->id,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $this->wakaSarpras->id,
    ]);
    AssetLoan::create([
        'asset_id' => $asset->id,
        'borrower_id' => $this->guru->id,
        'expected_return_at' => now()->addDays(2),
        'status' => 'pending',
        'purpose' => 'Presentasi kelas X-2',
    ]);
    $resource = BookableResource::create([
        'name' => 'Aula Sultan Agung',
        'type' => 'room',
        'capacity' => 300,
        'is_available' => true,
    ]);
    ResourceBooking::create([
        'bookable_resource_id' => $resource->id,
        'booked_by' => $this->guru->id,
        'purpose' => 'Seminar Olimpiade',
        'start_datetime' => now()->addDays(3),
        'end_datetime' => now()->addDays(3)->addHours(2),
        'status' => 'pending',
    ]);

    // 7. Keuangan: PettyCashTransaction (oleh TU dan satu self-request oleh Kepsek) + BudgetPlan (submitted)
    $fund = PettyCashFund::create([
        'fund_name' => 'Kas Operasional Harian',
        'custodian_id' => $this->tu->id,
        'imprest_amount' => 5000000,
        'current_balance' => 3500000,
        'is_active' => true,
    ]);
    PettyCashTransaction::create([
        'fund_id' => $fund->id,
        'type' => 'pengeluaran',
        'amount' => 250000,
        'description' => 'ATK Rapat Kurikulum',
        'transaction_date' => '2026-10-19',
        'category' => 'atk',
        'approval_status' => 'pending',
        'requested_by' => $this->tu->id,
    ]);
    PettyCashTransaction::create([
        'fund_id' => $fund->id,
        'type' => 'pengeluaran',
        'amount' => 999999,
        'description' => 'Pengajuan Pribadi Kepsek (Harus Dikecualikan)',
        'transaction_date' => '2026-10-19',
        'category' => 'lainnya',
        'approval_status' => 'pending',
        'requested_by' => $this->kepsek->id,
    ]);
    BudgetPlan::create([
        'fiscal_year' => '2026/2027',
        'title' => 'RAPBS SMA Islam Sultan Agung 1 2026/2027',
        'status' => 'submitted',
        'total_revenue_planned' => 2000000000,
        'total_expense_planned' => 1850000000,
        'created_by' => $this->tu->id,
    ]);

    // A. Siswa dan orang tua diblokir
    $this->actingAs($this->studentUser)->get(route('workflows.pending'))->assertForbidden();
    $this->actingAs($this->parentUser)->get(route('workflows.pending'))->assertForbidden();
    Sanctum::actingAs($this->studentUser);
    $this->getJson('/api/v1/approvals/pending')->assertForbidden();

    // B. Kepala Sekolah melihat workflow surat, disposisi, jurnal mengajar, petty cash TU (BUKAN milik sendiri), dan budget plan
    $this->actingAs($this->kepsek)->get(route('workflows.pending'))
        ->assertOk()
        ->assertSee('Persetujuan Surat Resmi')
        ->assertSee('Rapat Yayasan Bulan November')
        ->assertSee('Sumpah Pemuda 1928')
        ->assertSee('ATK Rapat Kurikulum')
        ->assertSee('RAPBS SMA Islam Sultan Agung 1 2026/2027')
        ->assertDontSee('Pengajuan Pribadi Kepsek');

    Sanctum::actingAs($this->kepsek);
    $kepsekApi = $this->getJson('/api/v1/approvals/pending?module=finance')->assertOk();
    $financeTitles = collect($kepsekApi->json('data.items'))->pluck('title')->implode(' | ');
    expect($financeTitles)->toContain('ATK Rapat Kurikulum')
        ->and($financeTitles)->toContain('RAPBS SMA Islam Sultan Agung 1 2026/2027')
        ->and($financeTitles)->not->toContain('Pengajuan Pribadi Kepsek');

    // C. Waka Kurikulum melihat RPP, Silabus (draft), dan Jurnal Mengajar
    $this->actingAs($this->wakaKurikulum)->get(route('workflows.pending', ['module' => 'academic']))
        ->assertOk()
        ->assertSee('Modul Ajar Pergerakan Nasional')
        ->assertSee('Sejarah Indonesia (Kelas X)')
        ->assertSee('Sumpah Pemuda 1928');

    // D. Waka Sarpras melihat Peminjaman Aset dan Reservasi Ruangan
    $this->actingAs($this->wakaSarpras)->get(route('workflows.pending', ['module' => 'sarpras']))
        ->assertOk()
        ->assertSee('Proyektor Epson EB-X500')
        ->assertSee('Aula Sultan Agung');

    // E. Wali Kelas X-1 hanya melihat izin siswa X-1 (tidak X-2)
    $this->actingAs($this->waliX1)->get(route('workflows.pending'))
        ->assertOk()
        ->assertSee('Naufal Maulana')
        ->assertDontSee('Siswa Sebelah');

    // F. Staf TU melihat Perubahan Data Diri (K4)
    $this->actingAs($this->tu)->get(route('workflows.pending', ['module' => 'profile']))
        ->assertOk()
        ->assertSee('Nama Ibu Kandung');

    // G. Pejabat Plt. Waka Kurikulum melalui PositionAssignment mewarisi kotak masuk akademik
    $wakaPos = Position::firstOrCreate(
        ['code' => 'waka_kurikulum'],
        ['name' => 'Waka Kurikulum', 'is_system' => true, 'is_active' => true]
    );
    PositionAssignment::create([
        'position_id' => $wakaPos->id,
        'user_id' => $this->waliX1->id,
        'starts_on' => now()->subDay()->toDateString(),
        'ends_on' => now()->addMonth()->toDateString(),
        'is_acting' => true,
    ]);
    $this->actingAs($this->waliX1)->get(route('workflows.pending', ['module' => 'academic']))
        ->assertOk()
        ->assertSee('Modul Ajar Pergerakan Nasional');
});

test('K2 student ledger compiles chronological history, excludes unvalidated achievements, uses configured or PositionAssignment principal, renders PDF, and enforces anti-IDOR', function () {
    $sy1 = SchoolYear::create(['name' => '2025/2026', 'start_date' => '2025-07-14', 'end_date' => '2026-06-20', 'status' => SchoolYear::STATUS_CLOSED]);
    $sy2 = SchoolYear::create(['name' => '2026/2027', 'start_date' => '2026-07-13', 'end_date' => '2027-06-18', 'status' => SchoolYear::STATUS_ACTIVE]);

    $oldClass = Classroom::factory()->create(['name' => 'X-1 Tahun Lalu', 'grade_level' => 'X', 'academic_year' => '2025/2026', 'teacher_id' => $this->waliX1->id]);

    ClassEnrollment::create([
        'student_id' => $this->student->id,
        'classroom_id' => $oldClass->id,
        'school_year_id' => $sy1->id,
        'status' => ClassEnrollment::STATUS_PROMOTED,
        'started_on' => '2025-07-14',
        'ended_on' => '2026-06-20',
    ]);
    ClassEnrollment::create([
        'student_id' => $this->student->id,
        'classroom_id' => $this->classroomX1->id,
        'school_year_id' => $sy2->id,
        'status' => ClassEnrollment::STATUS_ACTIVE,
        'started_on' => '2026-07-13',
    ]);

    StudentMutation::create([
        'student_id' => $this->student->id,
        'type' => 'in',
        'mutation_date' => '2025-07-14',
        'reason' => 'Siswa Baru PPDB',
        'target_school' => 'SMP Islam Sultan Agung 1',
    ]);

    $subject = Subject::factory()->create(['name' => 'Pendidikan Agama Islam', 'code' => 'PAI', 'category' => 'ciri_khas', 'kkm' => 78]);
    Grade::create([
        'student_id' => $this->student->id,
        'subject_id' => $subject->id,
        'academic_year_id' => $this->academicYear->id,
        'type' => 'pas',
        'score' => 94,
        'recorded_by' => $this->waliX1->id,
    ]);
    ReportCard::create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'classroom_id' => $this->classroomX1->id,
        'total_score' => 94,
        'average_score' => 94,
        'rank_in_class' => 2,
        'homeroom_notes' => 'Aktif dan berakhlak baik.',
    ]);

    Attendance::create(['student_id' => $this->student->id, 'academic_year_id' => $this->academicYear->id, 'date' => '2026-08-01', 'status' => 'H', 'recorded_by' => $this->waliX1->id]);
    Attendance::create(['student_id' => $this->student->id, 'academic_year_id' => $this->academicYear->id, 'date' => '2026-08-02', 'status' => 'S', 'recorded_by' => $this->waliX1->id]);

    Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Juara 1 OSN Informatika Provinsi',
        'field' => 'Matematika & Sains',
        'level' => 'PROVINSI',
        'organizer' => 'Puspresnas',
        'event_date' => '2026-08-10',
        'verification_status' => 'VALIDATED',
        'verified_by' => $this->wakaKurikulum->id,
        'verified_at' => now(),
    ]);
    Achievement::create([
        'student_id' => $this->student->id,
        'title' => 'Prestasi Belum Diverifikasi',
        'field' => 'Olahraga',
        'level' => 'KOTA',
        'organizer' => 'KONI',
        'event_date' => '2026-09-01',
        'verification_status' => 'PENDING_REVIEW',
    ]);

    $ekskul = Extracurricular::create(['name' => 'Pramuka Inti', 'advisor_id' => $this->waliX1->id]);
    $member = ExtracurricularMember::create([
        'extracurricular_id' => $ekskul->id,
        'student_id' => $this->student->id,
        'role' => 'anggota',
        'status' => 'active',
        'joined_at' => '2026-07-20',
    ]);
    ExtracurricularGrade::create([
        'extracurricular_member_id' => $member->id,
        'academic_year_id' => $this->academicYear->id,
        'grade' => 'A',
        'description' => 'Sangat baik dalam kepramukaan',
    ]);

    AlumniData::create([
        'student_id' => $this->student->id,
        'graduation_year' => 2027,
        'current_activity' => 'study',
        'institution_name' => 'Undip Semarang',
    ]);

    // Anti-IDOR: Siswa lain, Orang tua lain, dan Guru yang bukan wali/pengajar X-1 diblokir
    $this->actingAs($this->otherStudentUser)->get(route('students.ledger', $this->student))->assertForbidden();
    $this->actingAs($this->otherParent)->get(route('students.ledger', $this->student))->assertForbidden();
    $this->actingAs($this->guru)->get(route('students.ledger', $this->student))->assertForbidden();
    $this->actingAs($this->guru)->get(route('students.ledger.pdf', $this->student))->assertForbidden();

    // Siswa sendiri, orang tuanya, wali kelas (role wali_kelas), TU, dan Kepsek berhak melihat
    $this->actingAs($this->studentUser)->get(route('students.ledger', $this->student))->assertOk();
    $this->actingAs($this->parentUser)->get(route('students.ledger', $this->student))->assertOk();
    $this->actingAs($this->waliX1)->get(route('students.ledger', $this->student))->assertOk();

    $ledgerView = $this->actingAs($this->tu)->get(route('students.ledger', $this->student))
        ->assertOk()
        ->assertSee('Naufal Maulana')
        ->assertSee('Siti Aminah')
        ->assertSee('X-1 Tahun Lalu')
        ->assertSee('SMP Islam Sultan Agung 1')
        ->assertSee('Pendidikan Agama Islam')
        ->assertSee('Kelompok C (Ciri Khas)')
        ->assertSee('Juara 1 OSN Informatika Provinsi')
        ->assertSee('Pramuka Inti')
        ->assertSee('Undip Semarang')
        ->assertDontSee('Prestasi Belum Diverifikasi');

    // PDF format dinas menghasilkan file PDF yang valid
    $pdfResp = $this->actingAs($this->tu)->get(route('students.ledger.pdf', $this->student))
        ->assertOk();
    expect(str_starts_with($pdfResp->getContent(), '%PDF-'))->toBeTrue();
});
