<?php

use App\Models\AlumniData;
use App\Models\Classroom;
use App\Models\IncomingLetter;
use App\Models\Letter;
use App\Models\LetterDisposition;
use App\Models\LetterTemplate;
use App\Models\Position;
use App\Models\PositionAssignment;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Notification::fake();

    $this->tu = User::factory()->create(['role' => 'staf_tu', 'name' => 'Rina Staf TU']);
    $this->kepsek = User::factory()->create(['role' => 'kepala_sekolah', 'name' => 'Dr. Sulaiman Kepala Sekolah']);
    $this->guru = User::factory()->create(['role' => 'guru', 'name' => 'Budi Santoso, S.Pd.']);
    $this->guruLain = User::factory()->create(['role' => 'guru', 'name' => 'Guru Lain']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Fadlan Alumni Putra']);

    $this->classroom = Classroom::factory()->create(['name' => 'XII-1', 'grade_level' => 'XII']);
    $this->student = Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $this->classroom->id,
        'nis' => '230999',
        'nisn' => '0077665544',
        'nik' => '3374010101080001',
    ]);

    $this->template = LetterTemplate::factory()->create([
        'name' => 'Surat Keterangan Berkelakuan Baik',
        'code' => 'SKBB',
        'body_template' => 'Menerangkan siswa {{nama_siswa}} (NIS {{nis}}) untuk {{keperluan}}.',
        'required_fields' => ['keperluan'],
        'signer_name' => 'Dr. Sulaiman Kepala Sekolah',
        'signer_title' => 'Kepala Sekolah',
        'is_active' => true,
    ]);

    WorkflowDefinition::firstOrCreate(
        ['code' => 'letter_approval'],
        [
            'name' => 'Persetujuan Surat Resmi',
            'steps' => [['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48]],
            'is_active' => true,
        ]
    );
});

test('J1 letter edit, revision resubmission, Plt principal PositionAssignment approval, search via users.name, and QR revocation work end to end', function () {
    $letter = Letter::factory()->create([
        'template_id' => $this->template->id,
        'student_id' => $this->student->id,
        'created_by' => $this->tu->id,
        'issue_date' => '2026-08-18',
        'field_values' => ['keperluan' => 'Draft Awal'],
        'status' => Letter::STATUS_DRAFT,
    ]);

    // Staf TU mengajukan persetujuan kepala sekolah
    $this->actingAs($this->tu)->post(route('letters.submit-approval', $letter->id))
        ->assertRedirect(route('letters.show', $letter->id));

    $letter->refresh();
    expect($letter->status)->toBe(Letter::STATUS_PENDING);

    // Kepala sekolah mengembalikan untuk revisi
    $this->actingAs($this->kepsek)->post(route('workflows.revise', $letter->workflowRequest), [
        'remarks' => 'Perbaiki keterangan keperluan beasiswa.',
    ])->assertRedirect(route('workflows.pending'));

    expect($letter->fresh()->status)->toBe(Letter::STATUS_REVISION);

    // Staf TU mengedit draf berstatus revisi lalu mengajukan kembali
    $this->actingAs($this->tu)->get(route('letters.edit', $letter->id))->assertOk();
    $this->actingAs($this->tu)->put(route('letters.update', $letter->id), [
        'issue_date' => '2026-08-19',
        'student_id' => $this->student->id,
        'field_values' => ['keperluan' => 'Pendaftaran KIP Kuliah 2026'],
    ])->assertRedirect(route('letters.show', $letter->id));

    $this->actingAs($this->tu)->post(route('letters.submit-approval', $letter->id))
        ->assertRedirect(route('letters.show', $letter->id));
    expect($letter->fresh()->status)->toBe(Letter::STATUS_PENDING);

    // Guru biasa bukan pejabat tidak boleh menyetujui, namun Plt. Kepsek via PositionAssignment BOLEH menyetujui
    $this->actingAs($this->guru)->post(route('workflows.approve', $letter->workflowRequest->fresh()))
        ->assertForbidden();

    $pos = Position::firstOrCreate(
        ['code' => 'kepala_sekolah'],
        ['name' => 'Kepala Sekolah', 'is_system' => true, 'is_active' => true]
    );
    PositionAssignment::create([
        'position_id' => $pos->id,
        'user_id' => $this->guru->id,
        'starts_on' => now()->subDay()->toDateString(),
        'ends_on' => now()->addMonth()->toDateString(),
        'is_acting' => true,
    ]);

    $this->actingAs($this->guru)->post(route('workflows.approve', $letter->workflowRequest->fresh()))
        ->assertRedirect(route('workflows.pending'));

    $letter->refresh();
    expect($letter->status)->toBe(Letter::STATUS_APPROVED)
        ->and($letter->approved_by)->toBe($this->guru->id);

    // Setelah disetujui, Staf TU dapat menerbitkan dan verifikasi QR publik tampil valid tanpa bocorkan PII
    $this->actingAs($this->tu)->post(route('letters.issue', $letter->id))
        ->assertRedirect(route('letters.show', $letter->id));
    $letter->refresh();
    expect($letter->status)->toBe(Letter::STATUS_ISSUED)
        ->and($letter->letter_number)->toBe('001/SKBB/SMA-ISA/VIII/2026');

    $this->get(route('letters.verify', $letter->verification_token))
        ->assertOk()
        ->assertSee('001/SKBB/SMA-ISA/VIII/2026')
        ->assertDontSee('0077665544')
        ->assertDontSee('3374010101080001');

    // Pencarian di letters.index dan alumni.index melalui relasi users.name tanpa SQL error
    AlumniData::create([
        'student_id' => $this->student->id,
        'graduation_year' => 2026,
        'current_activity' => 'study',
        'institution_name' => 'UNNES Semarang',
    ]);

    $this->actingAs($this->tu)->get(route('letters.index', ['search' => 'Fadlan']))->assertOk()->assertSee('001/SKBB/SMA-ISA/VIII/2026');
    $this->actingAs($this->kepsek)->get(route('alumni.index', ['search' => 'Fadlan']))->assertOk()->assertSee('Fadlan Alumni Putra');

    // Pencabutan surat mengubah status halaman verifikasi publik menjadi revoked
    $this->actingAs($this->tu)->post(route('letters.revoke', $letter->id), [
        'reason' => 'Kesalahan tahun pendaftaran.',
    ])->assertRedirect(route('letters.show', $letter->id));

    expect($letter->fresh()->status)->toBe(Letter::STATUS_REVOKED);
    $this->get(route('letters.verify', $letter->verification_token))
        ->assertOk()
        ->assertSee('DOKUMEN DIBATALKAN / DICABUT');
});

test('J2 incoming letters registration, private scan upload, principal disposition, recipient follow-up, archival, and anti-IDOR work securely', function () {
    // Guru biasa tidak berwenang membuka form create/store surat masuk
    $this->actingAs($this->guru)->get(route('incoming-letters.create'))->assertForbidden();

    $scanPdf = UploadedFile::fake()->createWithContent('surat_dinas.pdf', '%PDF-1.4 scan-content');

    // Staf TU mencatat surat masuk beserta pindaian PDF
    $createResp = $this->actingAs($this->tu)->post(route('incoming-letters.store'), [
        'reference_number' => '421.3/115/DISDIK/2026',
        'sender' => 'Dinas Pendidikan Provinsi Jawa Tengah',
        'subject' => 'Undangan Koordinasi Persiapan TKA SMA',
        'letter_date' => '2026-09-01',
        'received_date' => '2026-09-02',
        'category' => 'Dinas',
        'summary' => 'Harap menugaskan perwakilan kurikulum.',
        'attachment' => $scanPdf,
    ]);

    $letter = IncomingLetter::firstOrFail();
    $createResp->assertRedirect(route('incoming-letters.show', $letter));
    expect($letter->agenda_number)->toBe('001/SM/2026')
        ->and($letter->status)->toBe(IncomingLetter::STATUS_RECEIVED)
        ->and($letter->attachment_path)->not->toBeNull();
    Storage::disk('local')->assertExists($letter->attachment_path);

    // Guru biasa tidak dapat mengirim disposisi
    $this->actingAs($this->guru)->post(route('incoming-letters.dispose', $letter), [
        'to_user_id' => $this->guruLain->id,
        'instruction' => 'Tolong proses',
        'priority' => 'normal',
    ])->assertForbidden();

    // Kepala sekolah tidak boleh mendisposisikan ke siswa/eksternal
    $this->actingAs($this->kepsek)->post(route('incoming-letters.dispose', $letter), [
        'to_user_id' => $this->studentUser->id,
        'instruction' => 'Disposisi salah sasaran',
        'priority' => 'normal',
    ])->assertSessionHasErrors('to_user_id');

    // Kepala sekolah mendisposisikan ke Budi Guru
    $this->actingAs($this->kepsek)->post(route('incoming-letters.dispose', $letter), [
        'to_user_id' => $this->guru->id,
        'instruction' => 'Mohon hadiri rapat koordinasi dan laporkan hasilnya.',
        'priority' => 'segera',
        'due_at' => '2026-09-05',
    ])->assertRedirect(route('incoming-letters.show', $letter));

    $letter->refresh();
    $disposition = LetterDisposition::where('incoming_letter_id', $letter->id)->firstOrFail();
    expect($letter->status)->toBe(IncomingLetter::STATUS_DISPOSED)
        ->and($disposition->status)->toBe(LetterDisposition::STATUS_ASSIGNED);

    // Anti-IDOR: Guru lain yang tidak didisposisikan ditolak 403 saat melihat detail atau mengunduh lampiran
    $this->actingAs($this->guruLain)->get(route('incoming-letters.show', $letter))->assertForbidden();
    $this->actingAs($this->guruLain)->get(route('incoming-letters.attachment', $letter))->assertForbidden();

    // Penerima disposisi bisa melihat detail dan mengunduh pindaian surat
    $this->actingAs($this->guru)->get(route('incoming-letters.show', $letter))
        ->assertOk()
        ->assertSee('Undangan Koordinasi Persiapan TKA SMA');
    $this->actingAs($this->guru)->get(route('incoming-letters.attachment', $letter))->assertOk();

    // Guru lain ditolak memperbarui tindak lanjut disposisi orang lain
    $this->actingAs($this->guruLain)->post(route('letter-dispositions.follow-up', $disposition), [
        'status' => LetterDisposition::STATUS_DONE,
        'follow_up_notes' => 'Bajak laporan',
    ])->assertForbidden();

    // Penerima menindaklanjuti disposisi hingga selesai
    $this->actingAs($this->guru)->post(route('letter-dispositions.follow-up', $disposition), [
        'status' => LetterDisposition::STATUS_DONE,
        'follow_up_notes' => 'Rapat telah dihadiri, notulen tersimpan.',
    ])->assertRedirect(route('incoming-letters.show', $letter));

    expect($disposition->fresh()->status)->toBe(LetterDisposition::STATUS_DONE)
        ->and($disposition->fresh()->completed_at)->not->toBeNull()
        ->and($letter->fresh()->status)->toBe(IncomingLetter::STATUS_FOLLOWED_UP);

    // Staf TU mengarsipkan surat masuk -> selesai dan tidak menerima disposisi lanjutan
    $this->actingAs($this->tu)->post(route('incoming-letters.archive', $letter))
        ->assertRedirect(route('incoming-letters.show', $letter));
    expect($letter->fresh()->status)->toBe(IncomingLetter::STATUS_COMPLETED)
        ->and($letter->fresh()->archived_by)->toBe($this->tu->id);

    $this->actingAs($this->kepsek)->post(route('incoming-letters.dispose', $letter), [
        'to_user_id' => $this->guru->id,
        'instruction' => 'Disposisi terlambat',
        'priority' => 'normal',
    ])->assertRedirect()->assertSessionHas('error');
});
