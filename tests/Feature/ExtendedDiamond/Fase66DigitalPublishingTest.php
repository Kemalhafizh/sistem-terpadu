<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase66DigitalPublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_digital_magazines_index_and_subscription(): void
    {
        $user = User::factory()->create();

        $res = $this->actingAs($user)->get(route('publishing.magazines.index'));
        $res->assertStatus(200);
        $res->assertSee('Majalah Digital & Buletin Sekolah');

        $subRes = $this->actingAs($user)->postJson(route('publishing.magazines.subscribe'), [
            'email' => 'subscriber.alumni@sultanagung1.sch.id',
        ]);

        $subRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('magazine_subscribers', [
            'email' => 'subscriber.alumni@sultanagung1.sch.id',
        ]);
    }

    public function test_podcast_directory_and_creation(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);

        $res = $this->actingAs($teacher)->get(route('publishing.podcasts.index'));
        $res->assertStatus(200);
        $res->assertSee('Direktori Podcast & Audio Edukasi Islam');

        $createRes = $this->actingAs($teacher)->postJson(route('publishing.podcasts.store'), [
            'title' => 'Tadabbur Juz Amma Pagi Hari',
            'category' => 'islamic_studies',
            'description' => 'Kajian tafsir ringkas ayat-ayat pilihan.',
        ]);

        $createRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('podcasts', [
            'title' => 'Tadabbur Juz Amma Pagi Hari',
        ]);
    }
}
