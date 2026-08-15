<?php

namespace Tests\Feature\Student;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

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
        $response = $this->actingAs($this->admin)->get(route('internships.portal'));
        $response->assertStatus(200);
    }

    public function test_college_match_engine_renders_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('college_counseling.match_engine'));
        $response->assertStatus(200);
    }

    public function test_showcase_gallery_renders_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('showcase.gallery'));
        $response->assertStatus(200);
    }

    public function test_mini_bank_wallet_renders_successfully()
    {
        $response = $this->actingAs($this->admin)->get(route('mini_bank.wallet'));
        $response->assertStatus(200);
    }
}
