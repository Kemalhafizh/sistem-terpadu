<?php

use App\Models\User;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsGallery;
use App\Models\CmsTestimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('public');
    
    // Create users with various roles
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->humas = User::factory()->create(['role' => 'waka_humas']);
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ── 1. CMS ACCESS AUTHORIZATION (RBAC) ─────────────────────────────────

test('authorized roles can access cms dashboard', function () {
    $roles = [$this->superadmin, $this->admin, $this->humas];
    
    foreach ($roles as $user) {
        $response = $this->actingAs($user)->get(route('cms.dashboard'));
        $response->assertStatus(200);
    }
});

test('unauthorized roles are forbidden from cms dashboard', function () {
    $roles = [$this->guru, $this->student];
    
    foreach ($roles as $user) {
        $response = $this->actingAs($user)->get(route('cms.dashboard'));
        $response->assertStatus(403);
    }
});

test('guest is redirected to login from cms dashboard', function () {
    $response = $this->get(route('cms.dashboard'));
    $response->assertRedirect(route('login'));
});

// ── 2. LANDING PAGE DYNAMIC RENDERING ──────────────────────────────────

test('landing page loads dynamic welcome contents correctly', function () {
    // Create seed data
    $post = CmsPost::create([
        'title' => 'Berita Utama Sekolah',
        'slug' => 'berita-utama-sekolah',
        'excerpt' => 'Kutipan singkat berita utama',
        'body' => '<p>Konten lengkap berita utama sekolah...</p>',
        'category' => 'berita',
        'is_published' => true,
        'published_at' => now(),
        'author_id' => $this->humas->id
    ]);

    $gallery = CmsGallery::create([
        'title' => 'Lab Komputer',
        'image_path' => 'cms/gallery/lab.jpg',
        'category' => 'Fasilitas',
        'caption' => 'Laboratorium komputer sekolah terpadu',
        'order' => 1,
        'is_active' => true
    ]);

    $testimonial = CmsTestimonial::create([
        'name' => 'Dr. Akhmad',
        'role' => 'Alumni 2010',
        'content' => 'Sekolah yang sangat luar biasa membentuk kepribadian mulia.',
        'avatar_path' => 'cms/testimonials/avatar.jpg',
        'order' => 1,
        'is_active' => true
    ]);

    // Perform public welcome page request
    $response = $this->get(route('home'));

    $response->assertStatus(200)
        ->assertSee('Berita Utama Sekolah')
        ->assertSee('Kutipan singkat berita utama')
        ->assertSee('Lab Komputer')
        ->assertSee('Dr. Akhmad')
        ->assertSee('Alumni 2010');
});

// ── 3. PUBLIC PAGES & ARTICLES DETAILS RENDERING ───────────────────────

test('public can view dynamic custom page based on slug', function () {
    $page = CmsPage::create([
        'title' => 'Sejarah Sultan Agung',
        'slug' => 'sejarah-sekolah',
        'content' => '<h1>Sejarah Lengkap Sekolah Kita</h1><p>Berdiri sejak tahun 1980...</p>',
        'meta_description' => 'Mengenal lebih dalam sejarah berdirinya sekolah sultan agung.',
        'is_published' => true,
        'order' => 1,
        'show_in_nav' => true
    ]);

    $response = $this->get(route('public.page', 'sejarah-sekolah'));

    $response->assertStatus(200)
        ->assertSee('Sejarah Sultan Agung')
        ->assertSee('Berdiri sejak tahun 1980')
        ->assertSee('Mengenal lebih dalam sejarah berdirinya sekolah sultan agung.');
});

test('public can view single post and it increments view count', function () {
    $post = CmsPost::create([
        'title' => 'Prestasi Juara Debat Nasional',
        'slug' => 'prestasi-juara-debat-nasional',
        'excerpt' => 'Tim debat sekolah menyabet juara 1 tingkat nasional.',
        'body' => '<p>Siswa-siswi berprestasi berhasil memenangkan kompetisi debat.</p>',
        'category' => 'prestasi',
        'is_published' => true,
        'published_at' => now(),
        'author_id' => $this->humas->id,
        'view_count' => 10
    ]);

    // First request should increment view count
    $response = $this->get(route('public.post', 'prestasi-juara-debat-nasional'));

    $response->assertStatus(200)
        ->assertSee('Prestasi Juara Debat Nasional')
        ->assertSee('Siswa-siswi berprestasi berhasil memenangkan kompetisi debat.');

    $this->assertEquals(11, $post->fresh()->view_count);
});

// ── 4. CMS CRUD OPERATIONALS (PAGES, POSTS, GALLERIES, TESTIMONIALS) ───

test('authorized users can crud cms pages', function () {
    // 1. Create page
    $createResponse = $this->actingAs($this->humas)->post(route('cms.pages.store'), [
        'title' => 'Visi Misi Sekolah',
        'slug' => 'visi-misi',
        'content' => '<p>Mewujudkan generasi cerdas dan berkarakter islami.</p>',
        'meta_description' => 'Visi dan Misi sekolah.',
        'is_published' => true,
        'order' => 2,
        'show_in_nav' => true
    ]);
    
    $createResponse->assertRedirect(route('cms.pages.index'));
    $this->assertDatabaseHas('cms_pages', ['slug' => 'visi-misi', 'title' => 'Visi Misi Sekolah']);
    
    $page = CmsPage::where('slug', 'visi-misi')->first();

    // 2. Edit page
    $editResponse = $this->actingAs($this->humas)->put(route('cms.pages.update', $page->id), [
        'title' => 'Visi Misi Terkini',
        'slug' => 'visi-misi',
        'content' => '<p>Mewujudkan masa depan gemilang.</p>',
        'meta_description' => 'Visi dan Misi teranyar.',
        'is_published' => true,
        'order' => 1,
        'show_in_nav' => true
    ]);

    $editResponse->assertRedirect(route('cms.pages.index'));
    $this->assertDatabaseHas('cms_pages', ['slug' => 'visi-misi', 'title' => 'Visi Misi Terkini']);

    // 3. Delete page
    $deleteResponse = $this->actingAs($this->humas)->delete(route('cms.pages.destroy', $page->id));
    $deleteResponse->assertRedirect(route('cms.pages.index'));
    $this->assertDatabaseMissing('cms_pages', ['id' => $page->id]);
});

test('authorized users can crud cms posts with image cover upload', function () {
    $imageFile = UploadedFile::fake()->image('cover-berita.jpg', 800, 600);

    // 1. Create post
    $createResponse = $this->actingAs($this->humas)->post(route('cms.posts.store'), [
        'title' => 'Kegiatan Ramadhan Sekolah',
        'slug' => 'kegiatan-ramadhan-sekolah',
        'excerpt' => 'Ramadhan penuh berkah dengan pesantren kilat.',
        'body' => '<p>Pesantren kilat diikuti oleh seluruh siswa.</p>',
        'category' => 'kegiatan',
        'cover_image' => $imageFile,
        'is_published' => true,
    ]);

    $createResponse->assertRedirect(route('cms.posts.index'));
    $this->assertDatabaseHas('cms_posts', ['slug' => 'kegiatan-ramadhan-sekolah']);

    $post = CmsPost::where('slug', 'kegiatan-ramadhan-sekolah')->first();
    $this->assertNotNull($post->cover_image);
    Storage::disk('public')->assertExists($post->cover_image);

    // 2. Edit post
    $newImageFile = UploadedFile::fake()->image('new-cover.png');
    $editResponse = $this->actingAs($this->humas)->put(route('cms.posts.update', $post), [
        'title' => 'Pesantren Ramadhan Terkini',
        'slug' => 'pesantren-ramadhan-terkini',
        'excerpt' => 'Edit singkat.',
        'body' => '<p>Konten sudah diubah.</p>',
        'category' => 'kegiatan',
        'cover_image' => $newImageFile,
        'is_published' => true,
    ]);

    $editResponse->assertRedirect(route('cms.posts.index'));
    $this->assertDatabaseHas('cms_posts', ['slug' => 'pesantren-ramadhan-terkini']);
    
    $updatedPost = $post->fresh();
    Storage::disk('public')->assertExists($updatedPost->cover_image);
    Storage::disk('public')->assertMissing($post->cover_image); // Should delete old image

    // 3. Delete post
    $deleteResponse = $this->actingAs($this->humas)->delete(route('cms.posts.destroy', $updatedPost));
    $deleteResponse->assertRedirect(route('cms.posts.index'));
    $this->assertSoftDeleted('cms_posts', ['id' => $updatedPost->id]);
});

test('authorized users can crud cms galleries', function () {
    $imageFile = UploadedFile::fake()->image('sarana.jpg');

    // 1. Create gallery
    $createResponse = $this->actingAs($this->humas)->post(route('cms.galleries.store'), [
        'title' => 'Lapangan Basket',
        'category' => 'Fasilitas',
        'caption' => 'Tempat bermain basket',
        'image_path' => $imageFile,
        'order' => 1,
        'is_active' => true
    ]);

    $createResponse->assertRedirect(route('cms.galleries.index'));
    $this->assertDatabaseHas('cms_galleries', ['title' => 'Lapangan Basket']);

    $gallery = CmsGallery::where('title', 'Lapangan Basket')->first();
    Storage::disk('public')->assertExists($gallery->image_path);

    // 2. Edit gallery
    $editResponse = $this->actingAs($this->humas)->put(route('cms.galleries.update', $gallery->id), [
        'title' => 'Lapangan Olahraga Utama',
        'category' => 'Fasilitas',
        'caption' => 'Olahraga multifungsi',
        'order' => 3,
        'is_active' => true
    ]);

    $editResponse->assertRedirect(route('cms.galleries.index'));
    $this->assertDatabaseHas('cms_galleries', ['title' => 'Lapangan Olahraga Utama', 'order' => 3]);

    // 3. Delete gallery
    $deleteResponse = $this->actingAs($this->humas)->delete(route('cms.galleries.destroy', $gallery->id));
    $deleteResponse->assertRedirect(route('cms.galleries.index'));
    $this->assertDatabaseMissing('cms_galleries', ['id' => $gallery->id]);
    Storage::disk('public')->assertMissing($gallery->image_path);
});

test('authorized users can crud cms testimonials', function () {
    $avatarFile = UploadedFile::fake()->image('budi.jpg');

    // 1. Create testimonial
    $createResponse = $this->actingAs($this->humas)->post(route('cms.testimonials.store'), [
        'name' => 'Bapak Budi',
        'role' => 'Wali Murid',
        'content' => 'Sistem terpadu ini mempermudah monitoring anak.',
        'avatar_path' => $avatarFile,
        'is_active' => true,
        'order' => 1
    ]);

    $createResponse->assertRedirect(route('cms.testimonials.index'));
    $this->assertDatabaseHas('cms_testimonials', ['name' => 'Bapak Budi']);

    $testimonial = CmsTestimonial::where('name', 'Bapak Budi')->first();
    Storage::disk('public')->assertExists($testimonial->avatar_path);

    // 2. Edit testimonial
    $editResponse = $this->actingAs($this->humas)->put(route('cms.testimonials.update', $testimonial->id), [
        'name' => 'Bapak Budi Santoso',
        'role' => 'Wali Murid Kelas XII',
        'content' => 'Pelayanan guru sangat prima.',
        'is_active' => true,
        'order' => 2
    ]);

    $editResponse->assertRedirect(route('cms.testimonials.index'));
    $this->assertDatabaseHas('cms_testimonials', ['name' => 'Bapak Budi Santoso', 'order' => 2]);

    // 3. Delete testimonial
    $deleteResponse = $this->actingAs($this->humas)->delete(route('cms.testimonials.destroy', $testimonial->id));
    $deleteResponse->assertRedirect(route('cms.testimonials.index'));
    $this->assertDatabaseMissing('cms_testimonials', ['id' => $testimonial->id]);
    Storage::disk('public')->assertMissing($testimonial->avatar_path);
});

// ── 5. SEO & VALIDATION & FAILURES ─────────────────────────────────────

test('invalid custom pages or post slugs return 404 not found', function () {
    // Access non-existent page
    $responsePage = $this->get(route('public.page', 'slug-tidak-valid'));
    $responsePage->assertStatus(404);

    // Access non-existent post
    $responsePost = $this->get(route('public.post', 'slug-berita-tidak-valid'));
    $responsePost->assertStatus(404);
});

test('draft articles are not displayed publicly', function () {
    CmsPost::create([
        'title' => 'Berita Rahasia Draf',
        'slug' => 'berita-rahasia-draf',
        'excerpt' => 'Belum dirilis ke publik.',
        'body' => '<p>Konten rahasia draf.</p>',
        'category' => 'berita',
        'is_published' => false,
        'published_at' => null,
        'author_id' => $this->humas->id
    ]);

    // Try viewing single draft post
    $responseSingle = $this->get(route('public.post', 'berita-rahasia-draf'));
    $responseSingle->assertStatus(404);

    // Try viewing on welcome page
    $responseHome = $this->get(route('home'));
    $responseHome->assertDontSee('Berita Rahasia Draf');
});
