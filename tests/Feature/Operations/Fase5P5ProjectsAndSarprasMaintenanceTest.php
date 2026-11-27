<?php

use App\Events\NewNotificationEvent;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetMaintenanceTicket;
use App\Models\CharacterDimension;
use App\Models\Classroom;
use App\Models\GraduationBatch;
use App\Models\GraduationDecision;
use App\Models\MeetingNote;
use App\Models\P5Project;
use App\Models\P5ProjectAssessment;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\Room;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentMutation;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Services\Academic\EnrollmentService;
use App\Services\Academic\P5ProjectService;
use App\Services\WorkflowService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Cache::flush();
    Notification::fake();
});

test('WorkflowController::show eager-loads GraduationBatch and StudentMutation relations so rich A5 and A6 metadata renders without strict lazy-loading', function () {
    $waka = User::factory()->create(['role' => 'waka_kurikulum', 'must_change_password' => false])->fresh();
    $tu = User::factory()->create(['role' => 'staf_tu', 'must_change_password' => false])->fresh();
    $kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'must_change_password' => false])->fresh();

    $schoolYear = SchoolYear::create(['name' => '2026/2027', 'status' => SchoolYear::STATUS_ACTIVE]);
    $classroom = Classroom::factory()->create(['name' => 'XII-IPA-1', 'grade_level' => 'XII', 'school_year_id' => $schoolYear->id]);

    $studentUser1 = User::factory()->create(['role' => 'student', 'name' => 'Nadia Putri', 'must_change_password' => false]);
    $student1 = Student::factory()->create(['user_id' => $studentUser1->id, 'classroom_id' => $classroom->id, 'nis' => '270101']);
    $studentUser2 = User::factory()->create(['role' => 'student', 'name' => 'Farhan Akbar', 'must_change_password' => false]);
    $student2 = Student::factory()->create(['user_id' => $studentUser2->id, 'classroom_id' => $classroom->id, 'nis' => '270102']);

    $meetingNote = MeetingNote::create([
        'title' => 'Rapat Pleno Dewan Guru Kelulusan 2027',
        'type' => 'rapat_guru',
        'meeting_date' => '2027-05-02',
        'start_time' => '09:00:00',
        'created_by' => $waka->id,
        'status' => 'finalized',
    ]);

    $gradBatch = GraduationBatch::create([
        'school_year_id' => $schoolYear->id,
        'graduation_year' => 2027,
        'sk_number' => '421.3/SK-LULUS/205/2027',
        'meeting_note_id' => $meetingNote->id,
        'status' => GraduationBatch::STATUS_PENDING,
        'created_by' => $waka->id,
    ]);
    GraduationDecision::create([
        'graduation_batch_id' => $gradBatch->id,
        'student_id' => $student1->id,
        'classroom_id' => $classroom->id,
        'decision' => GraduationDecision::DECISION_GRADUATED,
    ]);
    GraduationDecision::create([
        'graduation_batch_id' => $gradBatch->id,
        'student_id' => $student2->id,
        'classroom_id' => $classroom->id,
        'decision' => GraduationDecision::DECISION_NOT_GRADUATED,
    ]);

    $mutation = StudentMutation::create([
        'student_id' => $student1->id,
        'classroom_id' => $classroom->id,
        'school_year_id' => $schoolYear->id,
        'type' => StudentMutation::TYPE_OUT,
        'mutation_date' => '2026-11-26',
        'reason' => 'Pindah domisili keluarga ke Bandung',
        'target_school' => 'SMA Negeri 3 Bandung',
        'letter_number' => '422/MUT/091/2026',
        'status' => StudentMutation::STATUS_PENDING,
        'billing_review_status' => StudentMutation::BILLING_CLEARED,
        'requested_by' => $tu->id,
    ]);

    WorkflowDefinition::firstOrCreate(
        ['code' => 'graduation_approval'],
        ['name' => 'Pengesahan SK Kelulusan Kelas XII', 'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]]]
    );
    WorkflowDefinition::firstOrCreate(
        ['code' => 'mutation_approval'],
        ['name' => 'Persetujuan Mutasi Siswa', 'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]]]
    );

    $workflowService = app(WorkflowService::class);
    $gradReq = $workflowService->initiate('graduation_approval', $waka, $gradBatch, 'Mohon pengesahan SK');
    $mutReq = $workflowService->initiate('mutation_approval', $tu, $mutation, 'Mohon persetujuan mutasi');

    $this->actingAs($kepsek)
        ->get(route('workflows.show', $gradReq))
        ->assertOk()
        ->assertSee('TA 2026/2027')
        ->assertSee('Rapat Pleno Dewan Guru Kelulusan 2027')
        ->assertSee('1 lulus')
        ->assertSee('1 belum lulus');

    $this->actingAs($kepsek)
        ->get(route('workflows.show', $mutReq))
        ->assertOk()
        ->assertSee('Nadia Putri')
        ->assertSee('(XII-IPA-1)')
        ->assertSee('SMA Negeri 3 Bandung');
});

test('D4 Projek P5 supports Koordinator P5 management, facilitator classroom-scoped MB/SB/BSH/SAB assessments, honest null unassessed dimensions, PDF & API Rapor P5, and strict Anti-IDOR', function () {
    $schoolYear = SchoolYear::create([
        'name' => '2026/2027',
        'start_date' => '2026-07-13',
        'end_date' => '2027-06-19',
        'status' => SchoolYear::STATUS_ACTIVE,
    ]);

    // Assign a regular teacher as Koordinator P5 via PositionAssignment (L2)
    $koordinatorP5 = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'must_change_password' => false]);
    $p5Pos = Position::where('code', 'koordinator_p5')->firstOrFail();
    PositionAssignment::create([
        'position_id' => $p5Pos->id,
        'user_id' => $koordinatorP5->id,
        'starts_on' => now()->subDays(3)->toDateString(),
    ]);
    $koordinatorP5 = $koordinatorP5->fresh();

    $facilitatorX1 = User::factory()->create(['role' => 'teacher', 'account_type' => 'guru', 'must_change_password' => false]);
    $unrelatedTeacher = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'must_change_password' => false]);
    $waliX1 = User::factory()->create(['role' => 'guru', 'account_type' => 'guru', 'name' => 'Ibu Ratna, S.Pd.', 'must_change_password' => false]);

    $classX1 = Classroom::factory()->create([
        'name' => 'X-1',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
        'teacher_id' => $waliX1->id,
    ]);
    $classX2 = Classroom::factory()->create([
        'name' => 'X-2',
        'grade_level' => 'X',
        'academic_year' => '2026/2027',
        'school_year_id' => $schoolYear->id,
    ]);

    $parentA = User::factory()->create(['role' => 'parent', 'must_change_password' => false]);
    $studentUserA = User::factory()->create(['role' => 'student', 'name' => 'Aisyah Zahra', 'must_change_password' => false]);
    $studentA = Student::factory()->create([
        'user_id' => $studentUserA->id,
        'parent_id' => $parentA->id,
        'classroom_id' => $classX1->id,
        'nis' => '261011',
        'nisn' => '0099110011',
        'status' => 'active',
    ]);

    $otherParent = User::factory()->create(['role' => 'orang_tua', 'must_change_password' => false]);
    $studentUserB = User::factory()->create(['role' => 'siswa', 'name' => 'Rafi Pratama', 'must_change_password' => false]);
    $studentB = Student::factory()->create([
        'user_id' => $studentUserB->id,
        'parent_id' => $otherParent->id,
        'classroom_id' => $classX2->id,
        'nis' => '261022',
        'status' => 'active',
    ]);

    app(EnrollmentService::class)->enroll($studentA, $classX1, $schoolYear, '2026-07-13');
    app(EnrollmentService::class)->enroll($studentB, $classX2, $schoolYear, '2026-07-13');

    $officialDimensions = app(P5ProjectService::class)->ensureOfficialDimensions();
    expect($officialDimensions->count())->toBeGreaterThanOrEqual(6);

    $gotongRoyong = CharacterDimension::where('name', 'Bergotong Royong')->where('category', 'p5')->firstOrFail();
    $bernalarKritis = CharacterDimension::where('name', 'Bernalar Kritis')->where('category', 'p5')->firstOrFail();

    // 1. Unassigned regular teacher cannot create P5 project
    $this->actingAs($unrelatedTeacher)
        ->post(route('p5.projects.store'), [
            'school_year_id' => $schoolYear->id,
            'grade_level' => 'X',
            'theme' => 'kearifan_lokal',
            'title' => 'Projek Tidak Sah',
            'dimensions' => [
                [
                    'character_dimension_id' => $gotongRoyong->id,
                    'element' => 'Kolaborasi',
                    'target_achievement' => 'Capaian kolaborasi',
                ],
            ],
        ])
        ->assertForbidden();

    // 2. Koordinator P5 creates Grade X project (Phase E inferred automatically) with 2 dimensions & X-1 facilitator
    $this->actingAs($koordinatorP5)
        ->post(route('p5.projects.store'), [
            'school_year_id' => $schoolYear->id,
            'grade_level' => 'X',
            'theme' => 'kearifan_lokal',
            'title' => 'Jejak Budaya Pesisir Semarang',
            'description' => 'Eksplorasi sejarah kuliner dan tradisi masyarakat pesisir Semarang.',
            'dimensions' => [
                [
                    'character_dimension_id' => $gotongRoyong->id,
                    'element' => 'Kolaborasi',
                    'sub_element' => 'Kerja sama',
                    'target_achievement' => 'Membangun tim dan mengelola kerjasama untuk mencapai tujuan bersama sesuai target.',
                ],
                [
                    'character_dimension_id' => $bernalarKritis->id,
                    'element' => 'Memperoleh dan memproses informasi dan gagasan',
                    'sub_element' => 'Mengidentifikasi dan mengolah informasi',
                    'target_achievement' => 'Secara kritis mengklarifikasi serta menganalisis gagasan dan informasi yang kompleks.',
                ],
            ],
            'facilitators' => [
                ['user_id' => $facilitatorX1->id, 'classroom_id' => $classX1->id],
            ],
        ])
        ->assertRedirect();

    $project = P5Project::where('title', 'Jejak Budaya Pesisir Semarang')->firstOrFail();
    expect($project->phase)->toBe('E')
        ->and($project->dimensions()->count())->toBe(2);

    $dim1 = $project->dimensions()->where('character_dimension_id', $gotongRoyong->id)->firstOrFail();
    $dim2 = $project->dimensions()->where('character_dimension_id', $bernalarKritis->id)->firstOrFail();

    // 3. Unrelated teacher blocked from assessing X-1 student, and X-1 facilitator blocked from assessing X-2 student (Anti-IDOR)
    $this->actingAs($unrelatedTeacher)
        ->post(route('p5.projects.assessments.update', $project), [
            'assessments' => [
                $studentA->id => [
                    'student_id' => $studentA->id,
                    'predicates' => [$dim1->id => 'BSH'],
                ],
            ],
        ])
        ->assertForbidden();

    $this->actingAs($facilitatorX1)
        ->post(route('p5.projects.assessments.update', $project), [
            'assessments' => [
                $studentB->id => [
                    'student_id' => $studentB->id,
                    'predicates' => [$dim1->id => 'BSH'],
                ],
            ],
        ])
        ->assertForbidden();

    // Invalid predicate outside MB/SB/BSH/SAB fails validation
    $this->actingAs($facilitatorX1)
        ->post(route('p5.projects.assessments.update', $project), [
            'assessments' => [
                $studentA->id => [
                    'student_id' => $studentA->id,
                    'predicates' => [$dim1->id => 'A+'],
                ],
            ],
        ])
        ->assertSessionHasErrors('predicate');

    // 4. X-1 facilitator assesses ONLY dimension 1 (`SAB`) + process_notes, leaving dimension 2 unassessed (`null`)
    $this->actingAs($facilitatorX1)
        ->post(route('p5.projects.assessments.update', $project), [
            'classroom_id' => $classX1->id,
            'assessments' => [
                $studentA->id => [
                    'student_id' => $studentA->id,
                    'predicates' => [
                        $dim1->id => 'SAB',
                        $dim2->id => '',
                    ],
                    'process_notes' => 'Aisyah memimpin wawancara tokoh budaya dengan sangat santun dan sistematis.',
                ],
            ],
        ])
        ->assertRedirect(route('p5.projects.show', ['project' => $project, 'classroom_id' => $classX1->id]));

    expect(P5ProjectAssessment::where('student_id', $studentA->id)->count())->toBe(1);

    // 5. Web Index & Project Detail views render cleanly under strict Eloquent mode
    $this->actingAs($koordinatorP5)
        ->get(route('p5.projects.index'))
        ->assertOk()
        ->assertSee('Jejak Budaya Pesisir Semarang');

    $this->actingAs($facilitatorX1)
        ->get(route('p5.projects.show', ['project' => $project, 'classroom_id' => $classX1->id]))
        ->assertOk()
        ->assertSee('Aisyah Zahra')
        ->assertSee('SAB — Sangat Berkembang');

    // 6. Anti-IDOR on Rapor Projek P5 (Web, PDF, & Mobile API)
    $this->actingAs($studentUserB)->get(route('p5.reports.show', $studentA))->assertForbidden();
    $this->actingAs($otherParent)->get(route('p5.reports.show', $studentA))->assertForbidden();
    $this->actingAs($unrelatedTeacher)->get(route('p5.reports.pdf', $studentA))->assertForbidden();

    // Authorized student, parent, and homeroom teacher can view Rapor Projek P5; unassessed dimension stays honestly "Belum dinilai" (`null`)
    $this->actingAs($studentUserA)
        ->get(route('p5.reports.show', $studentA))
        ->assertOk()
        ->assertSee('Jejak Budaya Pesisir Semarang')
        ->assertSee('SAB — Sangat Berkembang')
        ->assertSee('Belum dinilai')
        ->assertSee('Aisyah memimpin wawancara tokoh budaya');

    $this->actingAs($parentA)
        ->get(route('p5.reports.show', $studentA))
        ->assertOk();

    $pdfResponse = $this->actingAs($waliX1)
        ->get(route('p5.reports.pdf', $studentA))
        ->assertOk();
    expect(str_starts_with($pdfResponse->getContent(), '%PDF-'))->toBeTrue();

    // Mobile API v1 Rapor P5
    Sanctum::actingAs($otherParent);
    $this->getJson('/api/v1/p5/report?student_uuid='.$studentA->uuid)->assertForbidden();

    Sanctum::actingAs($parentA);
    $apiReport = $this->getJson('/api/v1/p5/report?student_uuid='.$studentA->uuid)
        ->assertOk()
        ->json('data');

    expect($apiReport['phase'])->toBe('E')
        ->and($apiReport['projects'])->toHaveCount(1)
        ->and($apiReport['projects'][0]['dimensions'][0]['predicate'])->toBe('SAB')
        ->and($apiReport['projects'][0]['dimensions'][1]['predicate'])->toBeNull()
        ->and($apiReport['projects'][0]['process_notes'])->toContain('Aisyah memimpin wawancara');
});

test('I3 unified Sarpras QR damage reporting stores private evidence, notifies Waka Sarpras, enforces attachment Anti-IDOR, and syncs Asset & AssetMaintenance across full status lifecycle', function () {
    Storage::fake('local');
    Storage::fake('public');
    Event::fake([NewNotificationEvent::class]);

    $wakaSarpras = User::factory()->create(['role' => 'waka_sarpras', 'account_type' => 'guru', 'must_change_password' => false]);
    $studentReporter = User::factory()->create(['role' => 'student', 'name' => 'Rizky Ramadhan', 'must_change_password' => false]);
    $otherStudent = User::factory()->create(['role' => 'student', 'must_change_password' => false]);
    $merchant = User::factory()->create(['role' => 'merchant', 'must_change_password' => false]);

    $room = Room::create(['name' => 'Lab Fisika', 'location' => 'Gedung B Lantai 2']);
    $category = AssetCategory::create(['name' => 'Laboratorium', 'code' => 'LAB']);

    $assetA = Asset::create([
        'asset_code' => 'INV-LAB-001',
        'name' => 'Osiloskop Digital Rigol',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'purchase_date' => '2025-08-01',
        'purchase_price' => 8500000,
        'useful_life_years' => 5,
        'is_borrowable' => true,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $wakaSarpras->id,
    ]);

    $assetB = Asset::create([
        'asset_code' => 'INV-LAB-002',
        'name' => 'Power Supply Variabel',
        'category_id' => $category->id,
        'room_id' => $room->id,
        'condition' => 'good',
        'status' => 'available',
        'created_by' => $wakaSarpras->id,
    ]);

    // Auto-generated QR code points to canonical assets.scan UUID route
    expect($assetA->fresh()->qr_code)->toBe(route('assets.scan', $assetA->uuid));

    // Outside non-member account (`merchant`) blocked from school QR scan
    $this->actingAs($merchant)->get(route('assets.scan', $assetA))->assertForbidden();

    // 1. Student scans QR & submits damage report with photo -> stored strictly on private `local` disk!
    $this->actingAs($studentReporter)
        ->get(route('assets.scan', $assetA))
        ->assertOk()
        ->assertSee('Osiloskop Digital Rigol')
        ->assertSee(route('assets.damage-reports.store', $assetA), false);

    $this->actingAs($studentReporter)
        ->post(route('assets.damage-reports.store', $assetA), [
            'issue_description' => 'Layar LCD osiloskop bergaris vertikal dan channel 2 tidak terbaca.',
            'priority' => 'high',
            'photo' => UploadedFile::fake()->image('osiloskop-rusak.jpg'),
        ])
        ->assertRedirect();

    $ticket = AssetMaintenanceTicket::where('asset_id', $assetA->id)->firstOrFail();
    expect($ticket->uuid)->not->toBeEmpty()
        ->and($ticket->status)->toBe(AssetMaintenanceTicket::STATUS_PENDING)
        ->and($ticket->photo_disk)->toBe('local');

    Storage::disk('local')->assertExists($ticket->photo_evidence);
    Storage::disk('public')->assertMissing($ticket->photo_evidence);

    Event::assertDispatched(NewNotificationEvent::class, function (NewNotificationEvent $e) use ($wakaSarpras) {
        return $e->user->id === $wakaSarpras->id && $e->notification['type'] === 'asset_damage_reported';
    });

    // 2. Private attachment Anti-IDOR: reporter & Waka Sarpras allowed, other student blocked (403)
    $this->actingAs($studentReporter)
        ->get(route('assets.maintenances.tickets.attachment', $ticket))
        ->assertOk();
    $this->actingAs($wakaSarpras)
        ->get(route('assets.maintenances.tickets.attachment', $ticket))
        ->assertOk();
    $this->actingAs($otherStudent)
        ->get(route('assets.maintenances.tickets.attachment', $ticket))
        ->assertForbidden();

    // Student cannot transition ticket status
    $this->actingAs($studentReporter)
        ->post(route('assets.maintenances.tickets.status', $ticket), [
            'status' => 'resolved',
        ])
        ->assertForbidden();

    // 3. Unified Sarpras hub `assets.maintenances.index` shows the new QR damage ticket
    $this->actingAs($wakaSarpras)
        ->get(route('assets.maintenances.index'))
        ->assertOk()
        ->assertSee('Osiloskop Digital Rigol')
        ->assertSee('Layar LCD osiloskop bergaris vertikal');

    // 4. Status transitions: pending -> investigating -> repairing -> resolved (syncing Asset & creating corrective AssetMaintenance log)
    $this->actingAs($wakaSarpras)
        ->post(route('assets.maintenances.tickets.status', $ticket), [
            'status' => 'investigating',
            'resolution_notes' => 'Sedang dicek modul display oleh teknisi lab.',
        ])
        ->assertRedirect(route('assets.maintenances.index'));

    expect($ticket->fresh()->status)->toBe('investigating')
        ->and($ticket->fresh()->investigated_at)->not->toBeNull()
        ->and($assetA->fresh()->status)->toBe('repairing')
        ->and($assetA->fresh()->condition)->toBe('damaged');

    $this->actingAs($wakaSarpras)
        ->post(route('assets.maintenances.tickets.status', $ticket->fresh()), [
            'status' => 'repairing',
            'vendor' => 'CV Instrumen Presisi',
        ])
        ->assertRedirect(route('assets.maintenances.index'));

    expect($ticket->fresh()->status)->toBe('repairing')
        ->and($ticket->fresh()->repair_started_at)->not->toBeNull();

    $this->actingAs($wakaSarpras)
        ->post(route('assets.maintenances.tickets.status', $ticket->fresh()), [
            'status' => 'resolved',
            'cost' => 450000,
            'vendor' => 'CV Instrumen Presisi',
            'resolution_notes' => 'Kabel fleksibel LCD dan konektor BNC channel 2 telah diganti baru.',
        ])
        ->assertRedirect(route('assets.maintenances.index'));

    $resolvedTicket = $ticket->fresh();
    expect($resolvedTicket->status)->toBe('resolved')
        ->and($resolvedTicket->asset_maintenance_id)->not->toBeNull()
        ->and($assetA->fresh()->status)->toBe('available')
        ->and($assetA->fresh()->condition)->toBe('good');

    $correctiveLog = AssetMaintenance::findOrFail($resolvedTicket->asset_maintenance_id);
    expect($correctiveLog->type)->toBe('korektif')
        ->and((float) $correctiveLog->cost)->toBe(450000.0)
        ->and($correctiveLog->vendor)->toBe('CV Instrumen Presisi');

    // 5. Mobile API QR scan + report + terminal `scrapped` guard on assetB
    Sanctum::actingAs($studentReporter);
    $this->getJson('/api/v1/assets/scan/'.$assetB->uuid)
        ->assertOk()
        ->assertJsonPath('data.asset_code', 'INV-LAB-002');

    $reportApi = $this->postJson('/api/v1/assets/scan/'.$assetB->uuid.'/report', [
        'issue_description' => 'Trafo utama terbakar dan mengeluarkan bau hangus.',
        'priority' => 'critical',
    ])->assertCreated();

    $ticketB = AssetMaintenanceTicket::where('uuid', $reportApi->json('data.uuid'))->firstOrFail();

    $this->getJson('/api/v1/assets/damage-reports/my')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->actingAs($wakaSarpras)
        ->post(route('assets.maintenances.tickets.status', $ticketB), [
            'status' => 'scrapped',
            'resolution_notes' => 'Kerusakan total pada kumparan primer, dihapusbukukan.',
        ])
        ->assertRedirect(route('assets.maintenances.index'));

    expect($assetB->fresh()->status)->toBe('lost')
        ->and($assetB->fresh()->condition)->toBe('broken');

    // Scrapped asset ticket cannot be reopened back to repairing
    $this->actingAs($wakaSarpras)
        ->post(route('assets.maintenances.tickets.status', $ticketB->fresh()), [
            'status' => 'repairing',
        ])
        ->assertSessionHasErrors('status');
});
