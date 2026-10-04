<?php

use App\Exceptions\AiUnavailableException;
use App\Models\AlumniData;
use App\Models\BpaRuleExecution;
use App\Models\Classroom;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\AiCopilotService;
use App\Services\AlumniEnhancedService;
use App\Services\BusinessRuleEngineService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/*
 * The last three entries of the made-up-value DEBT list:
 * - the rule engine claimed a counseling ticket ("BK-" + rand), a WhatsApp to
 *   the parent, a payroll deduction and an SP1 that nothing ever created;
 * - alumni in a city without coordinates were pinned at a random spot
 *   around Semarang;
 * - "AI" exam questions without an AI were template text with a random
 *   answer key, which could be saved to the question bank.
 */

// ── Rule engine ─────────────────────────────────────────────────────────────

test('aturan yang cocok tidak lagi mengaku sudah menerbitkan tiket atau mengirim WA', function () {
    $engine = app(BusinessRuleEngineService::class);
    $engine->createRule([
        'name' => 'Kehadiran rendah', 'trigger_event' => 'attendance.checked',
        'conditions' => [['field' => 'attendance_percentage', 'operator' => '<', 'value' => 80]],
        'actions' => [['action_type' => 'issue_counseling_ticket'], ['action_type' => 'send_whatsapp_alert']],
    ]);

    $result = $engine->evaluateAndExecute('attendance.checked', ['attendance_percentage' => 70.0]);

    expect($result['executions'][0])
        ->matched->toBeTrue()
        ->status->toBe('NOT_EXECUTED')
        ->executed_actions_count->toBe(0)
        ->not_executed_actions->toBe(['issue_counseling_ticket', 'send_whatsapp_alert']);

    $log = BpaRuleExecution::sole();
    expect($log->status)->toBe('NOT_EXECUTED')
        ->and(collect($log->actions_executed)->pluck('result.status')->unique()->all())->toBe(['NOT_IMPLEMENTED'])
        ->and(json_encode($log->actions_executed))->not->toContain('ticket_no')
        ->not->toContain('SENT_TO_PARENT');
});

test('simulasi dan log eksekusi di konsol admin berterus terang', function () {
    app(BusinessRuleEngineService::class)->createRule([
        'name' => 'Kehadiran rendah', 'trigger_event' => 'attendance.checked',
        'conditions' => [['field' => 'attendance_percentage', 'operator' => '<', 'value' => 80]],
        'actions' => [['action_type' => 'issue_counseling_ticket']],
    ]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->from(route('admin.bpa.rules.index'))
        ->post(route('admin.bpa.rules.test-execute'), ['trigger_event' => 'attendance.checked'])
        ->assertSessionHas('success', fn ($message) => str_contains($message, 'cocok: 1') && str_contains($message, 'belum dijalankan'));

    $this->get(route('admin.bpa.rules.index'))->assertOk()->assertSee('1 belum dijalankan');
});

// ── Alumni map ──────────────────────────────────────────────────────────────

test('kota alumni tanpa koordinat tidak ditaruh acak di peta', function () {
    foreach (['Semarang', 'Kota Antah', 'Kota Antah'] as $city) {
        AlumniData::create(['student_id' => Student::factory()->create()->id, 'graduation_year' => 2024, 'current_activity' => 'study', 'city' => $city]);
    }

    $geo = app(AlumniEnhancedService::class)->getAlumniGeography()->keyBy('city');

    expect($geo['Semarang'])->lat->toBe(-6.966667)->lng->toBe(110.416664)
        ->and($geo['Kota Antah'])->lat->toBeNull()->lng->toBeNull()->total->toBe(2)
        ->and(app(AlumniEnhancedService::class)->getAlumniGeography()->all())->toBe($geo->values()->all());

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->get(route('alumni.network-map'))->assertOk()
        ->assertSee('Belum terpetakan')->assertSee('Kota Antah (2)');
});

// ── AI exam questions ───────────────────────────────────────────────────────

function cbtRequest(array $overrides = []): array
{
    return $overrides + [
        'subject_id' => Subject::factory()->create()->id,
        'material_text' => 'Proklamasi kemerdekaan Republik Indonesia dibacakan oleh Ir. Soekarno pada 17 Agustus 1945 di Jakarta.',
        'type' => 'multiple_choice', 'count' => 2, 'difficulty' => 'medium',
    ];
}

test('tanpa layanan AI tidak ada soal templat berkunci acak', function () {
    $teacher = User::factory()->create(['role' => 'guru']);

    $this->actingAs($teacher)->from(route('academic.copilot.cbt'))
        ->post(route('academic.copilot.cbt.generate'), cbtRequest(['provider' => 'local_fallback']))
        ->assertRedirect(route('academic.copilot.cbt'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, 'membutuhkan layanan AI'));

    expect(fn () => app(AiCopilotService::class)->generateExamQuestions(['material_text' => 'x']))
        ->toThrow(AiUnavailableException::class)
        ->and(QuestionBank::count())->toBe(0);
});

test('soal dari Gemini ditampilkan apa adanya', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => "```json\n".json_encode([
        ['question_text' => 'Siapa pembaca teks proklamasi?', 'type' => 'multiple_choice', 'options' => ['A' => 'Soekarno', 'B' => 'Hatta'], 'correct_answer' => 'A', 'explanation' => 'Ir. Soekarno.', 'difficulty' => 'medium'],
        ['question_text' => '', 'correct_answer' => 'B'],
    ])."\n```"]]]]]])]);

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->post(route('academic.copilot.cbt.generate'), cbtRequest(['provider' => 'gemini', 'api_key' => 'kunci-uji']))
        ->assertOk()
        ->assertSee('Siapa pembaca teks proklamasi?')
        ->assertDontSee('Jawaban Tepat:');
});

test('AI yang gagal atau menjawab tanpa soal menjadi pesan galat, bukan soal palsu', function (array $fake, string $expected) {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(...$fake)]);

    $this->actingAs(User::factory()->create(['role' => 'guru']))->from(route('academic.copilot.cbt'))
        ->post(route('academic.copilot.cbt.generate'), cbtRequest(['provider' => 'gemini', 'api_key' => 'kunci-rahasia']))
        ->assertRedirect(route('academic.copilot.cbt'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, $expected) && ! str_contains($m, 'kunci-rahasia'))
        ->assertSessionMissing('_old_input.api_key');
})->with([
    'HTTP 401' => [[['error' => 'unauthorized'], 401], 'HTTP 401'],
    'bukan JSON' => [[['candidates' => [['content' => ['parts' => [['text' => 'Maaf, saya tidak bisa.']]]]]]], 'tidak berisi soal'],
]);

// ── AI lesson plans (RPP) ───────────────────────────────────────────────────

function rppRequest(array $overrides = []): array
{
    return $overrides + [
        'subject_id' => Subject::factory()->create(['name' => 'Fisika'])->id,
        'classroom_id' => Classroom::factory()->create()->id,
        'topic' => 'Hukum Newton',
        'competencies' => 'Menganalisis gaya, Menerapkan hukum II Newton',
    ];
}

test('templat RPP dipilih sendiri, diberi label, dan memakai identitas sekolah dari pengaturan', function () {
    config(['school.name' => 'SMA Contoh Nusantara']);

    $rpp = $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->post(route('academic.copilot.rpp.generate'), rppRequest(['provider' => 'local_fallback']))
        ->assertOk()
        ->viewData('rppContent');

    // (The page layout carries the theme name elsewhere, so check the RPP itself.)
    expect($rpp)->toContain('Templat, bukan hasil AI')
        ->toContain('SMA Contoh Nusantara')
        ->toContain('Hukum Newton')
        ->not->toContain('Sultan Agung')
        ->not->toContain('2 x 45');
});

test('RPP dengan AI yang gagal menjadi pesan galat, bukan templat yang menyamar', function (string $provider, ?string $key, ?array $fake, string $expected) {
    if ($fake !== null) {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(...$fake)]);
    }

    $this->actingAs(User::factory()->create(['role' => 'guru']))->from(route('academic.copilot.rpp'))
        ->post(route('academic.copilot.rpp.generate'), rppRequest(['provider' => $provider, 'api_key' => $key]))
        ->assertRedirect(route('academic.copilot.rpp'))
        ->assertSessionHas('error', fn ($m) => str_contains($m, $expected) && ! str_contains($m, 'kunci-rahasia'))
        ->assertSessionMissing('_old_input.api_key');
})->with([
    'tanpa API key' => ['gemini', null, null, 'membutuhkan OpenAI atau Gemini'],
    'HTTP 500' => ['gemini', 'kunci-rahasia', [['error' => 'internal'], 500], 'HTTP 500'],
    'jawaban kosong' => ['gemini', 'kunci-rahasia', [['candidates' => [['content' => ['parts' => [['text' => '  ']]]]]]], 'Jawaban AI kosong'],
]);

test('koneksi AI yang putus tidak membocorkan URL berisi API key', function () {
    Http::fake(fn () => throw new ConnectionException('cURL error 28 for https://generativelanguage.googleapis.com/...?key=kunci-rahasia'));

    expect(fn () => app(AiCopilotService::class)->generateLessonPlan(['topic' => 'Optik'], 'kunci-rahasia', 'gemini'))
        ->toThrow(fn (AiUnavailableException $e) => expect($e->getMessage())->not->toContain('kunci-rahasia')->toContain('Gagal menghubungi'));
});

test('RPP dari Gemini ditampilkan apa adanya dan prompt memakai nama sekolah dari pengaturan', function () {
    config(['school.name' => 'SMA Contoh Nusantara']);
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '# RPP Hukum Newton dari AI']]]]]])]);

    $this->actingAs(User::factory()->create(['role' => 'guru']))
        ->post(route('academic.copilot.rpp.generate'), rppRequest(['provider' => 'gemini', 'api_key' => 'kunci-uji']))
        ->assertOk()
        ->assertViewHas('rppContent', '# RPP Hukum Newton dari AI');

    Http::assertSent(fn ($request) => str_contains($request->body(), 'SMA Contoh Nusantara') && ! str_contains($request->body(), 'Sultan Agung'));
});
