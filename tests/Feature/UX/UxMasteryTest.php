<?php

namespace Tests\Feature\UX;

use App\Models\User;
use App\Services\UX\AccessibilityService;
use App\Services\UX\PrintStylesheetService;
use App\Services\UX\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UxMasteryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
    }

    /** @test */
    public function test_37_1_wcag_accessibility_hardening()
    {
        $service = app(AccessibilityService::class);

        $log = $service->runAudit('/dashboard', $this->admin->name);
        $this->assertEquals('/dashboard', $log->page_route);

        $compliance = $service->getComplianceScore();
        $this->assertEquals(100.0, $compliance['compliance_score']);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('ux.accessibility.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('WCAG 2.2 AA+ Accessibility Hardening');
    }

    /** @test */
    public function test_37_2_advanced_i18n_and_arabic_rtl_support()
    {
        $service = app(TranslationService::class);

        $override = $service->overrideTranslation('ar', 'welcome.msg', 'مرحباً بكم', $this->admin->id);
        $this->assertEquals('ar', $override->locale);

        $completion = $service->getCompletionPercentage();
        $this->assertEquals(100.0, $completion['id']);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('ux.i18n.manager'));
        $response->assertStatus(200);
        $response->assertSee('Advanced i18n');
    }

    /** @test */
    public function test_37_3_enterprise_design_system_component_library()
    {
        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('ux.design_system.showcase'));
        $response->assertStatus(200);
        $response->assertSee('Enterprise Design System Component Library Showcase');
    }

    /** @test */
    public function test_37_4_print_optimized_stylesheet_engine()
    {
        $service = app(PrintStylesheetService::class);

        $layout = $service->generatePrintLayout('Rapor Siswa', 'A4', 'portrait');
        $this->assertEquals('A4', $layout['paper_size']);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('ux.print.preview'));
        $response->assertStatus(200);
        $response->assertSee('Print-Optimized Engine Preview');
    }
}
