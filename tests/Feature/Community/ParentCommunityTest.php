<?php

namespace Tests\Feature\Community;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentCommunityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_view_partnerships_index()
    {
        $response = $this->actingAs($this->user)->get(route('partnerships.index'));
        $response->assertStatus(200);
        $response->assertSee('School-Community Partnership Management');
    }

    public function test_can_view_volunteer_opportunities()
    {
        $response = $this->actingAs($this->user)->get(route('volunteers.opportunities'));
        $response->assertStatus(200);
        $response->assertSee('Volunteer Management');
    }

    public function test_can_view_mentorship_directory()
    {
        $response = $this->actingAs($this->user)->get(route('mentorship.directory'));
        $response->assertStatus(200);
        $response->assertSee('Alumni Mentorship Program Platform');
    }
}
