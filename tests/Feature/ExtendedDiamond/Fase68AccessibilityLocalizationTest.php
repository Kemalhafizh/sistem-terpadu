<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase68AccessibilityLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_translation_manager(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $transRes = $this->actingAs($admin)->get(route('accessibility.translations.index'));
        $transRes->assertStatus(200);
        $transRes->assertSee('Manajer Lokalisasi & Bahasa');

        $storeTransRes = $this->actingAs($admin)->postJson(route('accessibility.translations.store'), [
            'group_name' => 'system',
            'string_key' => 'welcome_back',
            'text_id' => 'Selamat Datang Kembali',
            'text_en' => 'Welcome Back',
            'text_ar' => 'مرحباً بعودتك',
        ]);

        $storeTransRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('localization_strings', [
            'group_name' => 'system',
            'string_key' => 'welcome_back',
        ]);
    }

    public function test_theme_preference_update(): void
    {
        $user = User::factory()->create();

        $themeRes = $this->actingAs($user)->get(route('accessibility.theme.index'));
        $themeRes->assertStatus(200);
        $themeRes->assertSee('Pengaturan Aksesibilitas & Desain Universal');

        $updateRes = $this->actingAs($user)->postJson(route('accessibility.theme.update'), [
            'color_mode' => 'high_contrast',
            'font_scaling' => '115%',
            'reduce_animations' => true,
            'dyslexic_font_enabled' => true,
        ]);

        $updateRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('theme_preferences', [
            'user_id' => $user->id,
            'color_mode' => 'high_contrast',
            'font_scaling' => '115%',
            'reduce_animations' => true,
            'dyslexic_font_enabled' => true,
        ]);
    }
}
