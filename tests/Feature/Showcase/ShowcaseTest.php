<?php

use App\Models\Classroom;
use App\Models\ShowcaseEndorsement;
use App\Models\Student;
use App\Models\StudentShowcase;
use App\Models\User;
use App\Services\Student\ShowcaseService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/*
 * The showcase used to be a stub: store() and endorse() said
 * "Karya berhasil dipublikasi" / "Dukungan berhasil diberikan" without saving,
 * the gallery was one hard-coded card ("Oleh: Ahmad Dahlan"), and create,
 * store, endorse and the profile page had no routes at all.
 */
beforeEach(function () {
    Storage::fake('public');
    $classroom = Classroom::factory()->create(['name' => 'XI MIPA 2']);
    $this->student = User::factory()->create(['role' => 'student', 'name' => 'Aisyah Rahma']);
    Student::factory()->create(['user_id' => $this->student->id, 'classroom_id' => $classroom->id]);
    $this->classmate = User::factory()->create(['role' => 'student', 'name' => 'Budi Santoso']);
    $this->teacher = User::factory()->create(['role' => 'guru', 'name' => 'Guru Pembimbing']);
});

function showcaseFor(User $student, array $attributes = []): StudentShowcase
{
    return StudentShowcase::create($attributes + [
        'student_id' => $student->id,
        'project_title' => 'Robot Pemilah Sampah',
        'description' => 'Robot sederhana berbasis sensor warna.',
        'is_public' => true,
    ]);
}

// ── Publishing ────────────────────────────────────────────────────────────

test('siswa mengunggah karya dan karyanya benar-benar tersimpan', function () {
    $response = $this->actingAs($this->student)->post(route('showcase.store'), [
        'project_title' => 'Aplikasi Jadwal Piket',
        'description' => 'Aplikasi web untuk membagi jadwal piket kelas.',
        'is_public' => '1',
        'media_urls' => ['https://github.com/contoh/piket', '', 'https://github.com/contoh/piket'],
        'cover_image' => UploadedFile::fake()->image('sampul.jpg', 800, 600),
    ]);

    $showcase = StudentShowcase::sole();
    $response->assertRedirect(route('showcase.show', $showcase))->assertSessionHas('success', 'Karya berhasil dipublikasikan.');

    expect($showcase->student_id)->toBe($this->student->id)
        ->and($showcase->project_title)->toBe('Aplikasi Jadwal Piket')
        ->and($showcase->is_public)->toBeTrue()
        ->and($showcase->view_count)->toBe(0)
        // Blank and duplicate links are dropped.
        ->and($showcase->media_urls)->toBe(['https://github.com/contoh/piket']);
    Storage::disk('public')->assertExists($showcase->cover_image_url);
});

test('karya privat disimpan dengan pesan yang jujur', function () {
    $this->actingAs($this->student)->post(route('showcase.store'), [
        'project_title' => 'Draf Esai', 'description' => 'Masih disusun.', 'is_public' => '0',
    ])->assertSessionHas('success', fn ($m) => str_contains($m, 'privat'));

    expect(StudentShowcase::sole()->is_public)->toBeFalse();
});

test('data tidak valid ditolak dan tidak ada yang tersimpan', function () {
    $this->actingAs($this->student)->post(route('showcase.store'), [
        'project_title' => '',
        'description' => '',
        'media_urls' => ['bukan-url', 'javascript:alert(1)'],
        'cover_image' => UploadedFile::fake()->create('virus.exe', 10),
    ])->assertSessionHasErrors(['project_title', 'description', 'media_urls.0', 'media_urls.1', 'cover_image']);

    expect(StudentShowcase::count())->toBe(0);
});

test('hanya siswa yang dapat mengunggah karya', function () {
    $this->actingAs($this->teacher)->get(route('showcase.create'))->assertForbidden();
    $this->actingAs($this->teacher)->post(route('showcase.store'), [
        'project_title' => 'X', 'description' => 'Y',
    ])->assertForbidden();

    // Admin is an operator, not a student: no blanket Gate::before pass any more.
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('showcase.store'), [
        'project_title' => 'X', 'description' => 'Y',
    ])->assertForbidden();

    expect(StudentShowcase::count())->toBe(0);
});

test('form unggah dan form ubah benar-benar berisi formulir', function () {
    $this->actingAs($this->student)->get(route('showcase.create'))
        ->assertOk()
        ->assertSee('name="project_title"', false)
        ->assertSee('name="cover_image"', false)
        ->assertSee('action="'.route('showcase.store').'"', false)
        ->assertDontSee('Formulir unggah karya baru.');

    $showcase = showcaseFor($this->student, ['media_urls' => ['https://contoh.id/video']]);
    $this->actingAs($this->student)->get(route('showcase.edit', $showcase))
        ->assertOk()
        ->assertSee('value="Robot Pemilah Sampah"', false)
        ->assertSee('https://contoh.id/video')
        ->assertSee('name="_method" value="PUT"', false);
});

// ── Ownership ─────────────────────────────────────────────────────────────

test('siswa hanya dapat mengubah dan menghapus karyanya sendiri', function () {
    $showcase = showcaseFor($this->student);
    $payload = ['project_title' => 'Diubah', 'description' => 'Diubah juga', 'is_public' => '1'];

    $this->actingAs($this->classmate)->get(route('showcase.edit', $showcase))->assertForbidden();
    $this->actingAs($this->classmate)->put(route('showcase.update', $showcase), $payload)->assertForbidden();
    $this->actingAs($this->classmate)->delete(route('showcase.destroy', $showcase))->assertForbidden();
    expect($showcase->fresh()->project_title)->toBe('Robot Pemilah Sampah');

    $this->actingAs($this->student)->put(route('showcase.update', $showcase), $payload)
        ->assertRedirect(route('showcase.show', $showcase));
    expect($showcase->fresh()->project_title)->toBe('Diubah');

    $this->actingAs($this->student)->delete(route('showcase.destroy', $showcase))->assertRedirect(route('showcase.my'));
    expect(StudentShowcase::count())->toBe(0);
});

test('mengganti atau menghapus sampul membuang file lama', function () {
    $this->actingAs($this->student)->post(route('showcase.store'), [
        'project_title' => 'A', 'description' => 'B', 'is_public' => '1',
        'cover_image' => UploadedFile::fake()->image('lama.png'),
    ]);
    $showcase = StudentShowcase::sole();
    $old = $showcase->cover_image_url;

    $this->actingAs($this->student)->put(route('showcase.update', $showcase), [
        'project_title' => 'A', 'description' => 'B', 'is_public' => '1',
        'cover_image' => UploadedFile::fake()->image('baru.png'),
    ]);
    $new = $showcase->fresh()->cover_image_url;
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($new);

    $this->actingAs($this->student)->put(route('showcase.update', $showcase), [
        'project_title' => 'A', 'description' => 'B', 'is_public' => '1', 'remove_cover' => '1',
    ]);
    expect($showcase->fresh()->cover_image_url)->toBeNull();
    Storage::disk('public')->assertMissing($new);
});

test('karya privat hanya terlihat oleh pemilik dan guru', function () {
    $private = showcaseFor($this->student, ['is_public' => false]);

    $this->actingAs($this->classmate)->get(route('showcase.show', $private))->assertForbidden();
    $this->actingAs($this->student)->get(route('showcase.show', $private))->assertOk();
    $this->actingAs($this->teacher)->get(route('showcase.show', $private))->assertOk();
});

// ── Endorsements ──────────────────────────────────────────────────────────

test('dukungan tersimpan sungguhan dan hanya sekali per orang', function () {
    $showcase = showcaseFor($this->student);

    $this->actingAs($this->teacher)->post(route('showcase.endorse', $showcase), [
        'endorsement_text' => 'Rancangan sensornya rapi, lanjutkan!',
    ])->assertRedirect(route('showcase.show', $showcase))->assertSessionHas('success');

    $endorsement = ShowcaseEndorsement::sole();
    expect($endorsement->endorser_id)->toBe($this->teacher->id)
        ->and($endorsement->endorsement_text)->toBe('Rancangan sensornya rapi, lanjutkan!');

    $this->actingAs($this->teacher)->post(route('showcase.endorse', $showcase), [
        'endorsement_text' => 'Dukungan kedua',
    ])->assertSessionHasErrors('endorsement_text');
    expect(ShowcaseEndorsement::count())->toBe(1);
});

test('siswa tidak dapat mendukung karyanya sendiri, termasuk lewat service', function () {
    $showcase = showcaseFor($this->student);

    $this->actingAs($this->student)->post(route('showcase.endorse', $showcase), [
        'endorsement_text' => 'Karya saya bagus sekali',
    ])->assertForbidden();

    expect(fn () => app(ShowcaseService::class)->endorse($showcase, $this->student, 'Bagus sekali'))
        ->toThrow(ValidationException::class);
    expect(ShowcaseEndorsement::count())->toBe(0);
});

test('karya privat tidak dapat didukung', function () {
    $private = showcaseFor($this->student, ['is_public' => false]);

    $this->actingAs($this->teacher)->post(route('showcase.endorse', $private), [
        'endorsement_text' => 'Bagus sekali',
    ])->assertForbidden();
    expect(ShowcaseEndorsement::count())->toBe(0);
});

test('database menolak dukungan ganda walau lolos pengecekan aplikasi', function () {
    $showcase = showcaseFor($this->student);
    ShowcaseEndorsement::create(['showcase_id' => $showcase->id, 'endorser_id' => $this->teacher->id, 'endorsement_text' => 'Pertama']);

    expect(fn () => ShowcaseEndorsement::create(['showcase_id' => $showcase->id, 'endorser_id' => $this->teacher->id, 'endorsement_text' => 'Kedua']))
        ->toThrow(UniqueConstraintViolationException::class);
});

// ── Pages show real data ────────────────────────────────────────────────────

test('galeri menampilkan karya publik nyata dengan nama dan kelas siswa', function () {
    showcaseFor($this->student, ['project_title' => 'Robot Pemilah Sampah']);
    showcaseFor($this->student, ['project_title' => 'Rahasia Pribadi', 'is_public' => false]);

    $this->actingAs($this->classmate)->get(route('showcase.gallery'))
        ->assertOk()
        ->assertSee('Robot Pemilah Sampah')
        ->assertSee('Aisyah Rahma')
        ->assertSee('XI MIPA 2')
        ->assertDontSee('Rahasia Pribadi')
        ->assertDontSee('Ahmad Dahlan')
        ->assertDontSee('Proyek Sains Terapan');
});

test('galeri kosong mengatakan apa adanya', function () {
    $this->actingAs($this->classmate)->get(route('showcase.gallery'))
        ->assertOk()
        ->assertSee('Belum ada karya siswa yang dipublikasikan.');
});

test('pencarian galeri dan paginasi', function () {
    foreach (range(1, 13) as $i) {
        showcaseFor($this->student, ['project_title' => "Karya Nomor {$i}"]);
    }
    showcaseFor($this->classmate, ['project_title' => 'Kincir Angin Mini']);

    $this->actingAs($this->teacher)->get(route('showcase.gallery', ['q' => 'Kincir']))
        ->assertOk()->assertSee('Kincir Angin Mini')->assertDontSee('Karya Nomor 1<');

    $page = $this->actingAs($this->teacher)->get(route('showcase.gallery'))->assertOk();
    expect($page->viewData('showcases')->total())->toBe(14)
        ->and($page->viewData('showcases')->count())->toBe(12);
});

test('karya saya menampilkan milik sendiri termasuk yang privat', function () {
    showcaseFor($this->student, ['project_title' => 'Publik Saya']);
    showcaseFor($this->student, ['project_title' => 'Privat Saya', 'is_public' => false]);
    showcaseFor($this->classmate, ['project_title' => 'Milik Teman']);

    $this->actingAs($this->student)->get(route('showcase.my'))
        ->assertOk()->assertSee('Publik Saya')->assertSee('Privat Saya')->assertDontSee('Milik Teman');
});

test('profil publik hanya menampilkan karya publik siswa itu, dengan hitungan nyata', function () {
    $published = showcaseFor($this->student, ['project_title' => 'Terbit']);
    showcaseFor($this->student, ['project_title' => 'Tersembunyi', 'is_public' => false]);
    ShowcaseEndorsement::create(['showcase_id' => $published->id, 'endorser_id' => $this->teacher->id, 'endorsement_text' => 'Mantap']);

    $this->actingAs($this->classmate)->get(route('showcase.profile', $this->student))
        ->assertOk()
        ->assertSee('Aisyah Rahma')->assertSee('XI MIPA 2')
        ->assertSee('Terbit')->assertDontSee('Tersembunyi')
        ->assertSee('1 karya dipublikasikan')->assertSee('1 dukungan diterima');

    // Only students have a showcase profile.
    $this->actingAs($this->classmate)->get(route('showcase.profile', $this->teacher))->assertNotFound();
});

test('jumlah dilihat hanya bertambah oleh pengunjung lain', function () {
    $showcase = showcaseFor($this->student);

    $this->actingAs($this->student)->get(route('showcase.show', $showcase))->assertOk();
    expect($showcase->fresh()->view_count)->toBe(0);

    $this->actingAs($this->classmate)->get(route('showcase.show', $showcase))->assertOk();
    $this->actingAs($this->teacher)->get(route('showcase.show', $showcase))->assertOk()->assertSee('2 kali dilihat');
    expect($showcase->fresh()->view_count)->toBe(2);
});
