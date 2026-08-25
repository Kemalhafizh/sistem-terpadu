<?php

namespace Tests\Feature\ExtendedDiamond;

use App\Models\Classroom;
use App\Models\ClassroomLayout;
use App\Models\KanbanBoard;
use App\Models\ScheduleBuilderDraft;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase64InteractiveUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_builder_renders_and_saves_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $res = $this->actingAs($admin)->get(route('interactive.schedule.index'));
        $res->assertStatus(200);
        $res->assertSee('Penyusun Jadwal Visual');

        $postRes = $this->actingAs($admin)->postJson(route('interactive.schedule.store'), [
            'name' => 'Draft Jadwal Baru Semester Genap',
            'schedule_data' => [
                ['day' => 'Senin', 'time_slot' => '07:00-08:30', 'teacher_id' => $admin->id, 'classroom_id' => 1],
            ],
        ]);

        $postRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('schedule_builder_drafts', [
            'name' => 'Draft Jadwal Baru Semester Genap',
        ]);
    }

    public function test_seating_chart_designer_renders_and_assigns_seat(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $classroom = Classroom::factory()->create();
        $student = Student::factory()->create(['classroom_id' => $classroom->id]);

        $layout = ClassroomLayout::create([
            'classroom_id' => $classroom->id,
            'layout_name' => 'Denah Utama',
            'grid_rows' => 5,
            'grid_columns' => 6,
        ]);

        $res = $this->actingAs($teacher)->get(route('interactive.seating.index'));
        $res->assertStatus(200);

        $assignRes = $this->actingAs($teacher)->postJson(route('interactive.seating.assign'), [
            'classroom_layout_id' => $layout->id,
            'student_id' => $student->id,
            'grid_row' => 1,
            'grid_column' => 2,
            'behavior_note' => 'Aktif bertanya di baris depan',
        ]);

        $assignRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('seat_assignments', [
            'classroom_layout_id' => $layout->id,
            'student_id' => $student->id,
            'grid_row' => 1,
            'grid_column' => 2,
        ]);
    }

    public function test_kanban_board_crud_and_move_task(): void
    {
        $user = User::factory()->create(['role' => 'guru']);

        $board = KanbanBoard::create([
            'title' => 'Papan Tugas Tim',
            'type' => 'project',
            'owner_id' => $user->id,
            'columns' => [
                ['id' => 'todo', 'title' => 'To Do'],
                ['id' => 'done', 'title' => 'Done'],
            ],
        ]);

        $taskRes = $this->actingAs($user)->postJson(route('interactive.kanban.store-task'), [
            'kanban_board_id' => $board->id,
            'title' => 'Membuat Soal Ujian Tengah Semester',
            'column_id' => 'todo',
            'priority' => 'high',
        ]);

        $taskRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $taskId = $taskRes->json('task.id');

        $moveRes = $this->actingAs($user)->postJson(route('interactive.kanban.move-task', $taskId), [
            'column_id' => 'done',
            'position_order' => 1,
        ]);

        $moveRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('kanban_tasks', [
            'id' => $taskId,
            'column_id' => 'done',
        ]);
    }
}
