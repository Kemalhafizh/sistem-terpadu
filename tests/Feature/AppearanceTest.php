<?php

use App\Models\AppearanceSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    AppearanceSetting::truncate();
});

test('unauthenticated guests are blocked from managing appearance settings', function () {
    $this->get(route('admin.appearance.index'))->assertRedirect('/login');
    $this->put(route('admin.appearance.update'), [])->assertRedirect('/login');
    $this->post(route('admin.appearance.reset'), [])->assertRedirect('/login');
});

test('non-admin users are blocked from managing appearance settings', function () {
    $teacher = User::factory()->create(['role' => 'guru']);

    $this->actingAs($teacher)->get(route('admin.appearance.index'))->assertStatus(403);
    $this->actingAs($teacher)->put(route('admin.appearance.update'), [])->assertStatus(403);
    $this->actingAs($teacher)->post(route('admin.appearance.reset'), [])->assertStatus(403);
});

test('admin can view appearance settings page successfully', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get(route('admin.appearance.index'));

    $response->assertStatus(200);
    $response->assertViewHas('appearance');
    $response->assertViewHas('fonts');
});

test('admin can update appearance branding text and color configurations', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->put(route('admin.appearance.update'), [
        'app_name' => 'SMA Sultan Agung Satu',
        'footer_text' => 'Hak Cipta Terpelihara 2026',
        'primary_color' => '#10b981',
        'secondary_color' => '#047857',
        'accent_color' => '#34d399',
        'sidebar_color' => '#064e3b',
        'font_family' => 'Outfit',
        'custom_css' => '.bg-custom { border: 1px solid red; }',
    ]);

    $response->assertRedirect(route('admin.appearance.index'));

    $this->assertDatabaseHas('appearance_settings', [
        'app_name' => 'SMA Sultan Agung Satu',
        'footer_text' => 'Hak Cipta Terpelihara 2026',
        'primary_color' => '#10b981',
        'secondary_color' => '#047857',
        'accent_color' => '#34d399',
        'sidebar_color' => '#064e3b',
        'font_family' => 'Outfit',
        'custom_css' => '.bg-custom { border: 1px solid red; }',
    ]);
});

test('admin can upload custom logo, favicon, and login background assets', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'admin']);

    $logo = UploadedFile::fake()->image('my_school_logo.png', 100, 100);
    $logoDark = UploadedFile::fake()->image('my_school_logo_dark.png', 100, 100);
    $favicon = UploadedFile::fake()->image('my_school_favicon.png', 32, 32);
    $loginBg = UploadedFile::fake()->image('my_school_login_bg.jpg', 800, 600);

    $response = $this->actingAs($admin)->put(route('admin.appearance.update'), [
        'app_name' => 'Branding Kustom SMA',
        'footer_text' => 'Copyright 2026',
        'primary_color' => '#3b82f6',
        'secondary_color' => '#1d4ed8',
        'accent_color' => '#60a5fa',
        'sidebar_color' => '#1e3a8a',
        'font_family' => 'Inter',
        'logo' => $logo,
        'logo_dark' => $logoDark,
        'favicon' => $favicon,
        'login_background' => $loginBg,
    ]);

    $response->assertRedirect(route('admin.appearance.index'));

    $setting = AppearanceSetting::first();

    $this->assertNotNull($setting->logo_path);
    $this->assertNotNull($setting->logo_dark_path);
    $this->assertNotNull($setting->favicon_path);
    $this->assertNotNull($setting->login_background_path);

    Storage::disk('public')->assertExists($setting->logo_path);
    Storage::disk('public')->assertExists($setting->logo_dark_path);
    Storage::disk('public')->assertExists($setting->favicon_path);
    Storage::disk('public')->assertExists($setting->login_background_path);
});

test('admin can reset appearance configuration back to factory default presets', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Set custom setting first
    $setting = AppearanceSetting::firstOrCreate([]);
    $setting->update([
        'app_name' => 'Sekolah Keren Sekali',
        'primary_color' => '#ff0000',
    ]);

    $response = $this->actingAs($admin)->post(route('admin.appearance.reset'));

    $response->assertRedirect(route('admin.appearance.index'));

    $fresh = AppearanceSetting::first();

    $this->assertEquals('Sistem Terpadu SMA ISA 1', $fresh->app_name);
    $this->assertEquals('#3b82f6', $fresh->primary_color);
    $this->assertEquals('#8b5cf6', $fresh->secondary_color);
    $this->assertEquals('#06b6d4', $fresh->accent_color);
    $this->assertEquals('#0f172a', $fresh->sidebar_color);
    $this->assertEquals('Inter', $fresh->font_family);
});

test('appearance settings are globally bound and resolved in app and guest views via view composer', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $setting = AppearanceSetting::firstOrCreate([]);
    $setting->update([
        'app_name' => 'Sistem Kustomisasi Kece',
    ]);

    // Requesting dashboard as authenticated user (will render layouts.app)
    $response = $this->actingAs($admin)->get(route('dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Sistem Kustomisasi Kece');

    // Requesting login page as guest (will render layouts.guest)
    auth()->logout();
    $responseGuest = $this->get('/login');
    $responseGuest->assertStatus(200);
    $responseGuest->assertSee('Sistem Kustomisasi Kece');
});
