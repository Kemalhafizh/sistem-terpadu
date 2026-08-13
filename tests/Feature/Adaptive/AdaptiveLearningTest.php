<?php

namespace Tests\Feature\Adaptive;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdaptiveLearningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
    }

    public function test_can_view_irt_calibration_dashboard()
    {
        $response = $this->get('/adaptive/calibration/dashboard');
        $response->assertStatus(200);
    }

    public function test_can_view_cat_admin_sessions()
    {
        $response = $this->get('/adaptive/cat/admin-sessions');
        $response->assertStatus(200);
    }

    public function test_can_view_learning_path_index()
    {
        $response = $this->get('/adaptive/learning-paths');
        $response->assertStatus(200);
    }

    public function test_can_view_mastery_heatmap()
    {
        $response = $this->get('/adaptive/mastery/heatmap/1');
        $response->assertStatus(200);
    }
}
