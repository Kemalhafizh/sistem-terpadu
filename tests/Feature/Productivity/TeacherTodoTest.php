<?php

/** @var TestCase|stdClass $this */

use App\Models\TeacherTodo;
use App\Models\User;
use Tests\TestCase;

beforeEach(function () {
    $this->guru = User::factory()->create(['role' => 'guru']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// ── INDEX ──────────────────────────────────────────────────────────────

test('guru can access todo list index page', function () {
    TeacherTodo::factory()->count(5)->create(['user_id' => $this->guru->id]);

    $response = $this->actingAs($this->guru)->get(route('todos.index'));

    $response->assertStatus(200);
    $response->assertViewIs('todos.index');
    $response->assertViewHas('todos');
    $response->assertViewHas('stats');
});

test('admin can also access todo list index page', function () {
    $response = $this->actingAs($this->admin)->get(route('todos.index'));
    $response->assertStatus(200);
});

test('student cannot access todo list', function () {
    $response = $this->actingAs($this->student)->get(route('todos.index'));
    $response->assertStatus(403);
});

test('guru can filter todos by status', function () {
    TeacherTodo::factory()->pending()->count(3)->create(['user_id' => $this->guru->id]);
    TeacherTodo::factory()->completed()->count(2)->create(['user_id' => $this->guru->id]);

    $response = $this->actingAs($this->guru)->get(route('todos.index', ['status' => 'pending']));
    $response->assertStatus(200);

    $response = $this->actingAs($this->guru)->get(route('todos.index', ['status' => 'completed']));
    $response->assertStatus(200);
});

test('guru only sees their own todos', function () {
    $otherGuru = User::factory()->create(['role' => 'guru']);
    TeacherTodo::factory()->create(['user_id' => $this->guru->id, 'title' => 'My Todo']);
    TeacherTodo::factory()->create(['user_id' => $otherGuru->id, 'title' => 'Other Todo']);

    $response = $this->actingAs($this->guru)->get(route('todos.index'));
    $response->assertStatus(200);
    $response->assertSee('My Todo');
    $response->assertDontSee('Other Todo');
});

// ── STORE ──────────────────────────────────────────────────────────────

test('guru can create a new todo', function () {
    $response = $this->actingAs($this->guru)->post(route('todos.store'), [
        'title' => 'Siapkan RPP Matematika',
        'description' => 'RPP untuk Bab 5 tentang Trigonometri.',
        'due_date' => now()->addDays(3)->toDateString(),
        'priority' => 'high',
        'category' => 'mengajar',
    ]);

    $response->assertRedirect(route('todos.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('teacher_todos', [
        'user_id' => $this->guru->id,
        'title' => 'Siapkan RPP Matematika',
        'priority' => 'high',
        'category' => 'mengajar',
        'is_completed' => false,
    ]);
});

test('todo store validates required fields', function () {
    $response = $this->actingAs($this->guru)->post(route('todos.store'), []);

    $response->assertSessionHasErrors(['title', 'priority', 'category']);
});

test('todo store validates enum values', function () {
    $response = $this->actingAs($this->guru)->post(route('todos.store'), [
        'title' => 'Test Todo',
        'priority' => 'invalid',
        'category' => 'invalid',
    ]);

    $response->assertSessionHasErrors(['priority', 'category']);
});

// ── TOGGLE ─────────────────────────────────────────────────────────────

test('guru can toggle todo completion status', function () {
    $todo = TeacherTodo::factory()->pending()->create(['user_id' => $this->guru->id]);

    // Toggle to completed
    $response = $this->actingAs($this->guru)->patch(route('todos.toggle', $todo));
    $response->assertRedirect();

    $todo->refresh();
    expect($todo->is_completed)->toBeTrue();
    expect($todo->completed_at)->not->toBeNull();

    // Toggle back to pending
    $response = $this->actingAs($this->guru)->patch(route('todos.toggle', $todo));
    $response->assertRedirect();

    $todo->refresh();
    expect($todo->is_completed)->toBeFalse();
    expect($todo->completed_at)->toBeNull();
});

test('guru cannot toggle another users todo', function () {
    $otherGuru = User::factory()->create(['role' => 'guru']);
    $todo = TeacherTodo::factory()->create(['user_id' => $otherGuru->id]);

    $response = $this->actingAs($this->guru)->patch(route('todos.toggle', $todo));
    $response->assertStatus(403);
});

// ── UPDATE ─────────────────────────────────────────────────────────────

test('guru can update their own todo', function () {
    $todo = TeacherTodo::factory()->create([
        'user_id' => $this->guru->id,
        'title' => 'Original Title',
    ]);

    $response = $this->actingAs($this->guru)->put(route('todos.update', $todo), [
        'title' => 'Updated Title',
        'description' => 'Updated description.',
        'due_date' => now()->addDays(5)->toDateString(),
        'priority' => 'high',
        'category' => 'rapat',
    ]);

    $response->assertRedirect(route('todos.index'));
    $response->assertSessionHas('success');

    $todo->refresh();
    expect($todo->title)->toBe('Updated Title');
    expect($todo->priority)->toBe('high');
    expect($todo->category)->toBe('rapat');
});

test('guru cannot update another users todo', function () {
    $otherGuru = User::factory()->create(['role' => 'guru']);
    $todo = TeacherTodo::factory()->create(['user_id' => $otherGuru->id]);

    $response = $this->actingAs($this->guru)->put(route('todos.update', $todo), [
        'title' => 'Hacked Title',
        'priority' => 'high',
        'category' => 'rapat',
    ]);

    $response->assertStatus(403);
});

// ── DESTROY ────────────────────────────────────────────────────────────

test('guru can delete their own todo', function () {
    $todo = TeacherTodo::factory()->create(['user_id' => $this->guru->id]);

    $response = $this->actingAs($this->guru)->delete(route('todos.destroy', $todo));

    $response->assertRedirect(route('todos.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('teacher_todos', ['id' => $todo->id]);
});

test('guru cannot delete another users todo', function () {
    $otherGuru = User::factory()->create(['role' => 'guru']);
    $todo = TeacherTodo::factory()->create(['user_id' => $otherGuru->id]);

    $response = $this->actingAs($this->guru)->delete(route('todos.destroy', $todo));
    $response->assertStatus(403);
});

// ── STATISTICS ──────────────────────────────────────────────────────────

test('todo statistics are calculated correctly', function () {
    // Create mix of todos with explicit dates
    TeacherTodo::factory()->pending()->count(3)->create(['user_id' => $this->guru->id]);
    TeacherTodo::factory()->completed()->count(2)->create(['user_id' => $this->guru->id]);
    TeacherTodo::factory()->create([
        'user_id' => $this->guru->id,
        'is_completed' => false,
        'due_date' => now()->subDays(2)->toDateString(),
    ]); // overdue
    TeacherTodo::factory()->create([
        'user_id' => $this->guru->id,
        'is_completed' => false,
        'due_date' => now()->toDateString(),
    ]); // due today
    TeacherTodo::factory()->create([
        'user_id' => $this->guru->id,
        'is_completed' => false,
        'priority' => 'high',
        'due_date' => now()->addDay()->toDateString(),
    ]); // high priority

    $response = $this->actingAs($this->guru)->get(route('todos.index'));
    $response->assertStatus(200);

    $stats = $response->viewData('stats');
    expect($stats['total'])->toBe(8);
    expect($stats['pending'])->toBe(6);
    expect($stats['completed'])->toBe(2);
    expect($stats['overdue'])->toBeGreaterThanOrEqual(1);
    expect($stats['due_today'])->toBeGreaterThanOrEqual(1);
});

// ── TODAY TASKS API ────────────────────────────────────────────────────

test('guru can fetch today tasks via API endpoint', function () {
    // Create 2 todos due today
    TeacherTodo::factory()->count(2)->create([
        'user_id' => $this->guru->id,
        'is_completed' => false,
        'due_date' => now()->toDateString(),
    ]);
    // Create 1 overdue todo
    TeacherTodo::factory()->create([
        'user_id' => $this->guru->id,
        'is_completed' => false,
        'due_date' => now()->subDays(2)->toDateString(),
    ]);

    $response = $this->actingAs($this->guru)
        ->getJson(route('todos.today'));

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'todos',
    ]);
    $response->assertJson(['success' => true]);
    expect($response->json('todos'))->toHaveCount(3);
});

test('student cannot access today tasks API', function () {
    $response = $this->actingAs($this->student)
        ->getJson(route('todos.today'));

    $response->assertStatus(403);
});
