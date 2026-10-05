<?php

namespace Tests\Feature\Adaptive;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $classroom = Classroom::factory()->create();

        $this->get('/adaptive/mastery/heatmap/'.$classroom->id)->assertStatus(200);
        // FASE 118.2: a class that does not exist is 404, not an empty heatmap.
        $this->get('/adaptive/mastery/heatmap/999999')->assertNotFound();
    }

    /** FASE 118: calibration and the CAT session list are for teachers and leadership. */
    public function test_students_and_parents_are_refused()
    {
        foreach (['siswa', 'orang_tua'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/adaptive/calibration/dashboard')->assertForbidden();
            $this->get('/adaptive/cat/admin-sessions')->assertForbidden();
        }
    }
}
