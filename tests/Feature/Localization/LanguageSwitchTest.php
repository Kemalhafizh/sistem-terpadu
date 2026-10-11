<?php

namespace Tests\Feature\Localization;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_switch_locale_to_english(): void
    {
        $response = $this->post(route('preferences.locale', 'en'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $this->assertEquals('en', session('locale'));
    }

    public function test_guest_can_switch_locale_to_arabic(): void
    {
        $response = $this->post(route('preferences.locale', 'ar'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'ar');
        $this->assertEquals('ar', session('locale'));
    }

    public function test_guest_can_switch_locale_to_indonesian(): void
    {
        $response = $this->post(route('preferences.locale', 'id'));

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'id');
        $this->assertEquals('id', session('locale'));
    }

    public function test_authenticated_user_persists_preferred_locale(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'preferred_locale' => 'id',
        ]);

        $response = $this->actingAs($user)->post(route('preferences.locale', 'ar'));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'preferred_locale' => 'ar',
        ]);
        $this->assertEquals('ar', session('locale'));
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $response = $this->post(route('preferences.locale', 'fr'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_json_locale_switch_returns_json_response(): void
    {
        $response = $this->postJson(route('preferences.locale', 'en'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'locale' => 'en',
        ]);
    }

    public function test_navigation_translations_exist_for_all_locales(): void
    {
        $locales = ['id', 'en', 'ar'];
        $testKeys = [
            'dashboard',
            'calendar',
            'library',
            'cbt',
            'tahsin',
            'inclusive_education',
            'header_counseling',
            'header_finance',
            'osis_mpk',
        ];

        foreach ($locales as $locale) {
            app()->setLocale($locale);
            foreach ($testKeys as $key) {
                $translation = __("navigation.{$key}");
                $this->assertNotEmpty($translation, "Missing translation for navigation.{$key} in {$locale}");
                $this->assertNotEquals("navigation.{$key}", $translation, "Untranslated key navigation.{$key} in {$locale}");
            }
        }
    }

    public function test_system_translations_exist_for_all_locales(): void
    {
        $locales = ['id', 'en', 'ar'];
        $testActionKeys = ['save', 'cancel', 'delete', 'edit', 'update', 'back', 'filter', 'search'];

        foreach ($locales as $locale) {
            app()->setLocale($locale);
            foreach ($testActionKeys as $key) {
                $translation = __("system.actions.{$key}");
                $this->assertNotEmpty($translation, "Missing translation for system.actions.{$key} in {$locale}");
                $this->assertNotEquals("system.actions.{$key}", $translation, "Untranslated key system.actions.{$key} in {$locale}");
            }
        }
    }

    public function test_arabic_layout_renders_with_rtl(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'preferred_locale' => 'ar',
        ]);

        $response = $this->actingAs($user)->withSession(['locale' => 'ar'])->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('Cairo', false);
    }
}
