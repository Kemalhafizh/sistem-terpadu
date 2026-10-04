<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

/*
 * Messages added with the journal access rule, the AI copilot errors and the
 * certificate signer were plain Indonesian strings, so an English or Arabic
 * user of the app or the web still got Indonesian. They now go through __().
 */

test('setiap kunci terjemahan ada di ketiga bahasa', function () {
    [$id, $en, $ar] = array_map(fn ($l) => json_decode(file_get_contents(lang_path("{$l}.json")), true), ['id', 'en', 'ar']);

    expect(array_keys($en))->toEqualCanonicalizing(array_keys($id))
        ->and(array_keys($ar))->toEqualCanonicalizing(array_keys($id));
});

test('penolakan jurnal di aplikasi mengikuti bahasa akun guru', function (string $locale, string $message) {
    Cache::forget('academic_year_current');
    AcademicYear::create(['year' => '2026/2027', 'semester' => 'Ganjil', 'is_active' => true]);
    Sanctum::actingAs(User::factory()->create(['role' => 'guru', 'preferred_locale' => $locale]));

    $this->postJson('/api/v1/teacher/journals', [
        'subject_id' => Subject::factory()->create()->id, 'classroom_id' => Classroom::factory()->create()->id,
        'teaching_date' => '2026-09-06', 'jam_ke' => 1, 'topic' => 'X', 'learning_activity' => 'X',
        'learning_method' => 'X', 'students_present' => 30, 'students_absent' => 0,
    ])->assertForbidden()->assertHeader('Content-Language', $locale)->assertJsonPath('message', $message);
})->with([
    'id' => ['id', 'Anda tidak mengajar mata pelajaran ini di kelas tersebut pada tahun ajaran aktif.'],
    'en' => ['en', 'You do not teach this subject in this class in the active academic year.'],
    'ar' => ['ar', 'أنت لا تدرّس هذه المادة في هذا الفصل خلال العام الدراسي الحالي.'],
]);

test('sertifikat tanpa kepala sekolah dan dokumen yang tidak ada dijawab dalam bahasa pengguna', function () {
    config(['school.name' => 'SMA Contoh Nusantara']);
    $studentUser = User::factory()->create(['role' => 'student', 'preferred_locale' => 'en']);
    $student = Student::factory()->create(['user_id' => $studentUser->id]);
    Sanctum::actingAs($studentUser);

    $this->postJson('/api/v1/debloat/certificates/issue', ['student_id' => $student->id, 'document_type' => 'IJAZAH', 'document_number' => 'IJZ-9'])
        ->assertStatus(422)
        ->assertJsonPath('message', "The principal's name has not been filled in under School Settings, so the certificate cannot be signed yet.");

    // Public verification, no account: the Accept-Language header decides.
    $this->app['auth']->forgetGuards();
    $this->getJson('/api/v1/certificates/verify/TIDAK-ADA', ['Accept-Language' => 'ar'])
        ->assertNotFound()
        ->assertJsonPath('data.message', 'لم يُعثر على المستند في السجلات الرسمية لـ SMA Contoh Nusantara.');
});

test('galat AI di halaman guru mengikuti bahasa akun', function () {
    $this->actingAs(User::factory()->create(['role' => 'guru', 'preferred_locale' => 'en']))
        ->from(route('academic.copilot.cbt'))
        ->post(route('academic.copilot.cbt.generate'), [
            'subject_id' => Subject::factory()->create()->id,
            'material_text' => str_repeat('Materi pembelajaran sejarah proklamasi. ', 3),
            'type' => 'multiple_choice', 'count' => 2, 'difficulty' => 'medium', 'provider' => 'gemini',
        ])
        ->assertSessionHas('error', 'Generating questions requires an AI service. Choose OpenAI or Gemini and enter its API key.');
});
