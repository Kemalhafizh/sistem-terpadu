<?php

use App\Models\CmsPost;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->humas = User::factory()->create(['role' => 'waka_humas']);
    $this->guru = User::factory()->create(['role' => 'guru']);
});

// ── 1. NEW PUBLIC PAGES ACCESSIBLE ─────────────────────────────────────

test('public about page is accessible', function () {
    $response = $this->get(route('public.about'));
    $response->assertStatus(200);
    $response->assertSee('Tentang Kami');
});

test('public contact page is accessible', function () {
    $response = $this->get(route('public.contact'));
    $response->assertStatus(200);
    $response->assertSee('Hubungi Kami');
});

test('public facilities page is accessible', function () {
    $response = $this->get(route('public.facilities'));
    $response->assertStatus(200);
    $response->assertSee('Fasilitas');
});

test('public achievements page is accessible and displays published prestasi posts', function () {
    CmsPost::create([
        'title' => 'Juara Olimpiade Fisika',
        'slug' => 'juara-olimpiade-fisika',
        'excerpt' => 'Siswa meraih juara 1 olimpiade fisika nasional.',
        'body' => '<p>Detail prestasi olimpiade fisika.</p>',
        'category' => 'prestasi',
        'is_published' => true,
        'published_at' => now(),
        'author_id' => $this->humas->id,
    ]);

    $response = $this->get(route('public.achievements'));
    $response->assertStatus(200);
    $response->assertSee('Juara Olimpiade Fisika');
});

test('public staff directory page is accessible and shows staff', function () {
    $teacher = User::factory()->create(['role' => 'guru', 'name' => 'Pak Ahmad Fauzi']);

    $response = $this->get(route('public.staff-directory'));
    $response->assertStatus(200);
    $response->assertSee('Pak Ahmad Fauzi');
});

test('public ppdb info redirects to spmb welcome', function () {
    $response = $this->get(route('public.ppdb-info'));
    $response->assertRedirect(route('spmb.welcome'));
});

test('public virtual tour page is accessible', function () {
    $response = $this->get(route('public.virtual-tour'));
    $response->assertStatus(200);
    $response->assertSee('Virtual Tour');
});

// ── 2. CMS ADMIN: SCHEDULED PUBLISHING ─────────────────────────────────

test('authorized user can schedule a post for future publication', function () {
    $post = CmsPost::create([
        'title' => 'Pengumuman Semester Depan',
        'slug' => 'pengumuman-semester-depan',
        'body' => '<p>Info semester depan.</p>',
        'category' => 'berita',
        'is_published' => false,
        'author_id' => $this->humas->id,
    ]);

    $futureDate = now()->addDays(7)->format('Y-m-d H:i:s');
    $response = $this->actingAs($this->humas)->post(route('cms.posts.schedule', $post->id), [
        'published_at' => $futureDate,
    ]);

    $response->assertRedirect(route('cms.posts.index'));
    $post->refresh();
    expect($post->is_published)->toBeTrue();
    expect($post->published_at->format('Y-m-d'))->toBe(now()->addDays(7)->format('Y-m-d'));
});

// ── 3. CMS ADMIN: TAG MANAGEMENT ───────────────────────────────────────

test('authorized user can view and update tag management', function () {
    // GET tags page
    $response = $this->actingAs($this->admin)->get(route('cms.tags.index'));
    $response->assertStatus(200);

    // POST update tags
    $response = $this->actingAs($this->admin)->post(route('cms.tags.index'), [
        'tags' => 'Berita, Prestasi, Kegiatan Islami, Akademik',
    ]);
    $response->assertRedirect(route('cms.tags.index'));
    expect(Setting::get('cms_tags'))->toBe('Berita, Prestasi, Kegiatan Islami, Akademik');
});

// ── 4. CMS ADMIN: MEDIA LIBRARY ────────────────────────────────────────

test('authorized user can upload and browse media library', function () {
    $imageFile = UploadedFile::fake()->image('banner-sekolah.jpg', 1200, 600);

    // Upload
    $response = $this->actingAs($this->admin)->post(route('cms.media.index'), [
        'file' => $imageFile,
    ]);
    $response->assertRedirect(route('cms.media.index'));
    Storage::disk('public')->assertExists('cms/media/'.$imageFile->hashName());

    // Browse
    $response = $this->actingAs($this->admin)->get(route('cms.media.index'));
    $response->assertStatus(200);
});

// ── 5. CMS ADMIN: SEO SETTINGS ─────────────────────────────────────────

test('authorized user can view and update SEO settings', function () {
    // GET SEO settings
    $response = $this->actingAs($this->admin)->get(route('cms.seo.index'));
    $response->assertStatus(200);

    // POST update SEO
    $response = $this->actingAs($this->admin)->post(route('cms.seo.index'), [
        'seo_title_template' => 'SISTA | {title} | Sultan Agung 1',
        'seo_meta_description' => 'Website Resmi SMA Islam Sultan Agung 1 Semarang - Terdepan Dalam Iman dan Ilmu.',
        'seo_og_title' => 'SMA Islam Sultan Agung 1 Semarang',
    ]);
    $response->assertRedirect(route('cms.seo.index'));
    expect(Setting::get('seo_title_template'))->toBe('SISTA | {title} | Sultan Agung 1');
    expect(Setting::get('seo_meta_description'))->toBe('Website Resmi SMA Islam Sultan Agung 1 Semarang - Terdepan Dalam Iman dan Ilmu.');
});

// ── 6. RBAC: GURU CANNOT ACCESS NEW CMS ADMIN FEATURES ─────────────────

test('guru cannot access new CMS admin endpoints', function () {
    $response = $this->actingAs($this->guru)->get(route('cms.tags.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->guru)->get(route('cms.media.index'));
    $response->assertStatus(403);

    $response = $this->actingAs($this->guru)->get(route('cms.seo.index'));
    $response->assertStatus(403);
});
