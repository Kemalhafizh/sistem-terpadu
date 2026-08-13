<?php
namespace Tests\Feature\Knowledge;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class KnowledgeManagementTest extends TestCase {
    use RefreshDatabase;
    protected function setUp(): void {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }
    public function test_can_view_knowledge_articles_index() {
        $response = $this->get('/knowledge/articles');
        $response->assertStatus(200);
    }
    public function test_can_view_lesson_study_index() {
        $response = $this->get('/knowledge/lesson-study');
        $response->assertStatus(200);
    }
    public function test_can_view_plc_groups_index() {
        $response = $this->get('/knowledge/plc');
        $response->assertStatus(200);
    }
    public function test_can_view_shared_resources_index() {
        $response = $this->get('/knowledge/resources');
        $response->assertStatus(200);
    }
}