<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test locale switching via preferences route.
     */
    public function test_locale_preference_switching_endpoint()
    {
        // 1. Guest locale switching
        $response = $this->from('/')
            ->post(route('preferences.locale', 'en'));

        $response->assertRedirect('/');
        $this->assertEquals('en', session('locale'));

        $response = $this->from('/')
            ->post(route('preferences.locale', 'ar'));

        $response->assertRedirect('/');
        $this->assertEquals('ar', session('locale'));

        // Invalid locale
        $response = $this->from('/')
            ->post(route('preferences.locale', 'fr'));

        $response->assertSessionHas('error');
        $this->assertNotEquals('fr', session('locale'));

        // 2. Authenticated user locale switching
        $user = User::factory()->create(['preferred_locale' => 'id']);

        $response = $this->actingAs($user)
            ->from('/')
            ->post(route('preferences.locale', 'en'));

        $response->assertRedirect('/');
        $this->assertEquals('en', session('locale'));
        $this->assertEquals('en', $user->fresh()->preferred_locale);
    }

    /**
     * Test that landing page renders correct locale strings and layout directions.
     */
    public function test_landing_page_respects_session_locale_and_sets_rtl_for_arabic()
    {
        // Indonesian (Default/Session)
        $response = $this->withSession(['locale' => 'id'])->get('/');
        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('Sulaone');

        // English
        $response = $this->withSession(['locale' => 'en'])->get('/');
        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('Building the Best');

        // Arabic
        $response = $this->withSession(['locale' => 'ar'])->get('/');
        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('بناء جيل');
    }

    /**
     * Test that SPMB Welcome page renders correct locale strings and layout directions.
     */
    public function test_spmb_welcome_respects_session_locale_and_sets_rtl_for_arabic()
    {
        // Indonesian
        $response = $this->withSession(['locale' => 'id'])->get(route('spmb.welcome'));
        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('Penerimaan Siswa Baru');

        // English
        $response = $this->withSession(['locale' => 'en'])->get(route('spmb.welcome'));
        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('New Student Admission');

        // Arabic
        $response = $this->withSession(['locale' => 'ar'])->get(route('spmb.welcome'));
        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('قبول الطلاب الجدد');
    }

    /**
     * Test that SPMB Create registration form respects locales and sets directions.
     */
    public function test_spmb_create_wizard_respects_session_locale_and_sets_rtl_for_arabic()
    {
        // Setup active academic year for SPMB registration open state
        AcademicYear::factory()->create([
            'is_active' => true,
            'year' => '2026/2027',
            'semester' => 'Ganjil', // Must be capitalized 'Ganjil' or 'Genap'
        ]);

        // English Form
        $response = $this->withSession(['locale' => 'en'])->get(route('spmb.create'));
        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
        $response->assertSee('Online Admission Form');
        $response->assertSee('Full name is required.'); // JS Validation strings
        $response->assertSee('Academic Year 2026/2027 - Semester Ganjil');

        // Arabic Form
        $response = $this->withSession(['locale' => 'ar'])->get(route('spmb.create'));
        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
        $response->assertSee('استمارة القبول الإلكترونية');
        $response->assertSee('الاسم الكامل مطلوب.'); // JS Validation strings
        $response->assertSee('العام الدراسي 2026/2027 - الفصل الدراسي Ganjil');
    }
}
