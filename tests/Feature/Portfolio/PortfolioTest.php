<?php

use App\Events\NewNotificationEvent;
use App\Models\PortfolioBadge;
use App\Models\PortfolioItem;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    // Fake the notifications event
    Event::fake([NewNotificationEvent::class]);
    Storage::fake('public');

    // Create a default student for test context
    $this->student = Student::factory()->create();
    $this->user = $this->student->user;
});

test('student can view their private portfolio page', function () {
    $this->actingAs($this->user)
        ->get(route('portfolios.mine'))
        ->assertStatus(200);
});

test('student can upload and add a new portfolio item successfully', function () {
    $file = UploadedFile::fake()->create('sertifikat_coding.pdf', 1024);

    $this->actingAs($this->user)
        ->post(route('portfolios.store'), [
            'title' => 'Juara 1 Hackathon Nasional',
            'description' => 'Membuat web app dalam waktu 24 jam.',
            'category' => 'prestasi',
            'date' => '2026-05-20',
            'external_link' => 'https://github.com/my-hackathon-project',
            'file' => $file,
        ])
        ->assertRedirect(route('portfolios.mine'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('portfolio_items', [
        'student_id' => $this->student->id,
        'title' => 'Juara 1 Hackathon Nasional',
        'category' => 'prestasi',
        'is_verified' => false,
        'is_public' => false,
    ]);

    // Check file uploaded
    $item = PortfolioItem::first();
    expect($item->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($item->file_path);
});

test('student can update unverified portfolio draf items', function () {
    $item = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Old Title',
        'is_verified' => false,
    ]);

    $this->actingAs($this->user)
        ->put(route('portfolios.update', $item), [
            'title' => 'New Title',
            'description' => 'New Description',
            'category' => 'proyek',
            'date' => '2026-05-21',
        ])
        ->assertRedirect(route('portfolios.mine'));

    expect($item->fresh()->title)->toBe('New Title');
    expect($item->fresh()->category)->toBe('proyek');
});

test('student cannot update or delete verified portfolio items', function () {
    $item = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Verified Achievement',
        'is_verified' => true,
    ]);

    // Attempt update
    $this->actingAs($this->user)
        ->put(route('portfolios.update', $item), [
            'title' => 'Hacked Title',
            'category' => 'prestasi',
        ])
        ->assertStatus(403);

    // Attempt delete
    $this->actingAs($this->user)
        ->delete(route('portfolios.destroy', $item))
        ->assertStatus(403);

    expect($item->fresh()->title)->toBe('Verified Achievement');
});

test('student can toggle public visibility of their portfolio items', function () {
    $item = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'is_public' => false,
    ]);

    $this->actingAs($this->user)
        ->post(route('portfolios.toggle-visibility', $item))
        ->assertRedirect(route('portfolios.mine'));

    expect($item->fresh()->is_public)->toBeTrue();

    $this->actingAs($this->user)
        ->post(route('portfolios.toggle-visibility', $item))
        ->assertRedirect(route('portfolios.mine'));

    expect($item->fresh()->is_public)->toBeFalse();
});

test('anyone can view student public portfolio showcase page', function () {
    // Verified & Public item
    $itemPublic = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Verified & Public Item',
        'is_verified' => true,
        'is_public' => true,
    ]);

    // Unverified/Private items
    $itemPrivate = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Private Item',
        'is_verified' => true,
        'is_public' => false,
    ]);

    $itemUnverified = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Unverified Item',
        'is_verified' => false,
        'is_public' => true,
    ]);

    $response = $this->get(route('portfolios.public', $this->student->uuid))
        ->assertStatus(200)
        ->assertSee('Verified & Public Item')
        ->assertDontSee('Private Item')
        ->assertDontSee('Unverified Item');
});

test('teachers can review student portfolio and verify items', function () {
    $teacher = User::factory()->create(['role' => 'guru']);
    $item = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'title' => 'Unverified Item',
        'is_verified' => false,
    ]);

    // Access review portal directory
    $this->actingAs($teacher)
        ->get(route('portfolios.index'))
        ->assertStatus(200);

    // Access student review page
    $this->actingAs($teacher)
        ->get(route('portfolios.review', $this->student))
        ->assertStatus(200);

    // Verify item
    $this->actingAs($teacher)
        ->post(route('portfolios.verify', $item), [
            'status' => 'verified',
        ])
        ->assertRedirect(route('portfolios.review', $this->student));

    expect($item->fresh()->is_verified)->toBeTrue();
    expect($item->fresh()->verified_by)->toBe($teacher->id);

    // Verify NewNotificationEvent was dispatched to student
    Event::assertDispatched(NewNotificationEvent::class, function ($event) {
        return $event->user->id === $this->user->id && $event->notification['type'] === 'portfolio_verified';
    });
});

test('teachers can award manual achievement badges to students', function () {
    $teacher = User::factory()->create(['role' => 'guru']);
    $badge = PortfolioBadge::create([
        'name' => 'Juara Kelas',
        'icon' => '🏆',
        'color' => '#f59e0b',
    ]);

    $this->actingAs($teacher)
        ->post(route('portfolios.award-badge', $this->student), [
            'badge_id' => $badge->id,
            'notes' => 'Peringkat 1 paralel Ujian Semester.',
        ])
        ->assertRedirect(route('portfolios.review', $this->student));

    // Verify student earned the badge
    expect($this->student->badges()->count())->toBe(1);
    expect($this->student->badges->first()->id)->toBe($badge->id);
    expect($this->student->badges->first()->pivot->notes)->toBe('Peringkat 1 paralel Ujian Semester.');
    expect($this->student->badges->first()->pivot->awarded_by)->toBe($teacher->id);

    // Verify NewNotificationEvent dispatched
    Event::assertDispatched(NewNotificationEvent::class, function ($event) {
        return $event->user->id === $this->user->id && $event->notification['type'] === 'portfolio_badge';
    });
});

test('students are blocked from verifying items or awarding badges', function () {
    $item = PortfolioItem::factory()->create([
        'student_id' => $this->student->id,
        'is_verified' => false,
    ]);

    $badge = PortfolioBadge::create([
        'name' => 'Badge',
        'icon' => '🏆',
    ]);

    // Try verify
    $this->actingAs($this->user)
        ->post(route('portfolios.verify', $item), ['status' => 'verified'])
        ->assertStatus(403);

    // Try award badge
    $this->actingAs($this->user)
        ->post(route('portfolios.award-badge', $this->student), ['badge_id' => $badge->id, 'notes' => 'Hacked'])
        ->assertStatus(403);
});
