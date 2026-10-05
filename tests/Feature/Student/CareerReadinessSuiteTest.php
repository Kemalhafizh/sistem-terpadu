<?php

namespace Tests\Feature\Student;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerReadinessSuiteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_internships_portal_renders_successfully()
    {
        // FASE 118: a student's own page.
        $response = $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('internships.portal'));
        $response->assertStatus(200);
    }

    public function test_college_match_engine_renders_successfully()
    {
        // FASE 118: a student's own page.
        $response = $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('college_counseling.match_engine'));
        $response->assertStatus(200);
    }

    public function test_showcase_gallery_renders_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('showcase.gallery'));
        $response->assertStatus(200);
    }

    public function test_mini_bank_wallet_renders_successfully()
    {
        // FASE 118: a student's own page.
        $response = $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('mini_bank.wallet'));
        $response->assertStatus(200);
    }

    /** FASE 118: parents cannot open the students' career pages, nor the bank teller. */
    public function test_parents_are_refused()
    {
        $parent = User::factory()->create(['role' => 'orang_tua']);
        foreach (['internships.portal', 'college_counseling.match_engine', 'mini_bank.wallet', 'mini_bank.teller'] as $route) {
            $this->actingAs($parent)->get(route($route))->assertForbidden();
        }
    }
}
