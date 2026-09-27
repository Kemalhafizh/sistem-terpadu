<?php

use App\Models\Classroom;
use App\Models\ExportJob;
use App\Models\Student;
use App\Models\User;
use App\Services\Content\ExportEngineService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake(ExportEngineService::DISK);
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Admin Export Interop']);
    $this->exportService = app(ExportEngineService::class);

    $classroom = Classroom::factory()->create(['name' => 'X-IPA-Ekspor']);
    $this->studentUser = User::factory()->create(['role' => 'student', 'name' => 'Aisyah Ekspor Nyata']);
    Student::factory()->create([
        'user_id' => $this->studentUser->id,
        'classroom_id' => $classroom->id,
        'nis' => 'NIS-7788',
        'nisn' => '0099887766',
        'gender' => 'P',
    ]);
});

test('every format writes a real file built from the database', function (string $format, string $ext) {
    $job = $this->exportService->queueExport('Data Siswa', 'students', $format, $this->user->id);

    expect($job->status)->toBe('completed')
        ->and($job->file_path)->toEndWith('.'.$ext);
    Storage::disk(ExportEngineService::DISK)->assertExists($job->file_path);
    expect($job->file_size_bytes)->toBe(Storage::disk(ExportEngineService::DISK)->size($job->file_path))
        ->and($job->file_size_bytes)->toBeGreaterThan(0);
})->with([
    'xlsx' => ['xlsx', 'xlsx'],
    'csv' => ['csv', 'csv'],
    'pdf' => ['pdf_a', 'pdf'],
    'json-ld' => ['json_ld', 'json'],
    'xml' => ['xml_dapodik', 'xml'],
]);

test('text formats carry the actual rows', function () {
    $csv = Storage::disk(ExportEngineService::DISK)->get(
        $this->exportService->queueExport('CSV Siswa', 'students', 'csv', $this->user->id)->file_path
    );
    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->toContain('NIS,NISN,Nama,Kelas')
        ->toContain('NIS-7788,0099887766,"Aisyah Ekspor Nyata",X-IPA-Ekspor,P');

    $json = json_decode(Storage::disk(ExportEngineService::DISK)->get(
        $this->exportService->queueExport('JSON Siswa', 'students', 'json_ld', $this->user->id)->file_path
    ), true);
    expect($json['@type'])->toBe('Dataset')
        ->and($json['size'])->toBe(1)
        ->and($json['data'][0])->toMatchArray(['nis' => 'NIS-7788', 'name' => 'Aisyah Ekspor Nyata', 'classroom' => 'X-IPA-Ekspor']);

    $xml = simplexml_load_string(Storage::disk(ExportEngineService::DISK)->get(
        $this->exportService->queueExport('XML Siswa', 'students', 'xml_dapodik', $this->user->id)->file_path
    ));
    expect((string) $xml['count'])->toBe('1')
        ->and((string) $xml->record[0]->nisn)->toBe('0099887766');
});

test('every entity exports without sensitive columns', function (string $entity) {
    $dataset = $this->exportService->dataset($entity);

    expect($dataset['headings'])->toHaveCount(count($dataset['keys']));
    foreach ($dataset['keys'] as $key) {
        expect(strtolower($key))->not->toContain('address')
            ->not->toContain('password')
            ->not->toContain('salary')
            ->not->toContain('bank');
    }
})->with(array_keys(ExportEngineService::ENTITIES));

test('admin can generate and download the stored file', function () {
    $this->actingAs($this->user)->get(route('exports.index'))->assertOk()->assertSee('Ekspor Data Sekolah');

    $this->actingAs($this->user)->post(route('exports.generate'), [
        'job_name' => 'Ekspor Keuangan',
        'entity_type' => 'finance',
        'format' => 'xlsx',
    ])->assertRedirect()->assertSessionHas('success');

    $job = ExportJob::where('job_name', 'Ekspor Keuangan')->firstOrFail();
    expect($job->status)->toBe('completed');

    $this->actingAs($this->user)->get(route('exports.download', $job->id))
        ->assertOk()
        ->assertDownload('Ekspor Keuangan.xlsx');
});

test('roles only export the data their job covers', function () {
    $kurikulum = User::factory()->create(['role' => 'waka_kurikulum']);

    $this->actingAs($kurikulum)->post(route('exports.generate'), [
        'job_name' => 'Coba Keuangan',
        'entity_type' => 'finance',
        'format' => 'csv',
    ])->assertSessionHasErrors('entity_type');

    $this->actingAs($kurikulum)->post(route('exports.generate'), [
        'job_name' => 'Nilai Semester',
        'entity_type' => 'grades',
        'format' => 'csv',
    ])->assertSessionHasNoErrors();

    $this->actingAs($this->studentUser)->get(route('exports.index'))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'guru']))->get(route('exports.index'))->assertForbidden();
});

test('a non-admin cannot download or see someone else\'s export', function () {
    $job = $this->exportService->queueExport('Rahasia Admin', 'students', 'csv', $this->user->id);
    $tu = User::factory()->create(['role' => 'staf_tu']);

    $this->actingAs($tu)->get(route('exports.download', $job->id))->assertNotFound();
    $this->actingAs($tu)->get(route('exports.history'))->assertOk()->assertDontSee('Rahasia Admin');
    $this->actingAs($this->user)->get(route('exports.history'))->assertOk()->assertSee('Rahasia Admin');
});

test('a missing file is reported instead of served as a fake', function () {
    $job = $this->exportService->queueExport('Hilang', 'inventory', 'csv', $this->user->id);
    Storage::disk(ExportEngineService::DISK)->delete($job->file_path);

    $this->actingAs($this->user)->from(route('exports.index'))->get(route('exports.download', $job->id))
        ->assertRedirect(route('exports.index'))
        ->assertSessionHas('error');
});
