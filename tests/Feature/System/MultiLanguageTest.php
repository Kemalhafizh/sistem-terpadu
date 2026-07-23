<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin', 'preferred_locale' => 'id']);
});

test('user can update preferred locale to english and arabic via preferences controller', function () {
    // Switch to English
    $responseEn = $this->actingAs($this->admin)
        ->post(route('preferences.locale', 'en'));

    $responseEn->assertRedirect();
    $this->assertEquals('en', session('locale'));
    $this->assertEquals('en', $this->admin->fresh()->preferred_locale);

    // Switch to Arabic
    $responseAr = $this->actingAs($this->admin)
        ->post(route('preferences.locale', 'ar'));

    $responseAr->assertRedirect();
    $this->assertEquals('ar', session('locale'));
    $this->assertEquals('ar', $this->admin->fresh()->preferred_locale);
});

test('system renders english translation when locale is en', function () {
    $this->admin->update(['preferred_locale' => 'en']);
    $this->actingAs($this->admin->fresh());
    app()->setLocale('en');

    $this->assertEquals('Dashboard', __('navigation.dashboard'));
    $this->assertEquals('Real-Time Event Streaming Bus & Webhook Hub', __('system.webhooks.title'));
    $this->assertEquals('Save', __('Simpan'));
    $this->assertEquals('Delete', __('Hapus'));
    $this->assertEquals('Print PDF', __('Cetak PDF'));

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('Dashboard');
});

test('system renders arabic translation and rtl direction when locale is ar', function () {
    $this->admin->update(['preferred_locale' => 'ar']);
    $this->actingAs($this->admin->fresh());
    app()->setLocale('ar');

    $this->assertEquals('لوحة التحكم', __('navigation.dashboard'));
    $this->assertEquals('ناقل الأحداث المباشر ومركز الويب هوك', __('system.webhooks.title'));
    $this->assertEquals('حفظ', __('Simpan'));
    $this->assertEquals('حذف', __('Hapus'));
    $this->assertEquals('طباعة PDF', __('Cetak PDF'));

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
    $response->assertSee('dir="rtl"', false);
    $response->assertSee('لوحة التحكم');
});

test('invalid locale selection returns error', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('preferences.locale', 'fr'));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});
