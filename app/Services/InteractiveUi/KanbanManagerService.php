<?php

declare(strict_types=1);

namespace App\Services\InteractiveUi;

use App\Models\KanbanBoard;
use App\Models\KanbanTask;
use App\Models\User;

class KanbanManagerService
{
    /**
     * Create a new Kanban board with default columns
     */
    public function createBoard(User $user, string $title, string $type = 'general'): KanbanBoard
    {
        return KanbanBoard::create([
            'title' => $title,
            'type' => $type,
            'owner_id' => $user->id,
            'columns' => [
                ['id' => 'todo', 'title' => 'Rencana / To Do', 'color' => '#64748b'],
                ['id' => 'in_progress', 'title' => 'Sedang Dikerjakan', 'color' => '#3b82f6'],
                ['id' => 'review', 'title' => 'Review / Evaluasi', 'color' => '#f59e0b'],
                ['id' => 'done', 'title' => 'Selesai', 'color' => '#10b981'],
            ],
        ]);
    }

    /**
     * Move task to a new column and update ordering
     */
    public function moveTask(int $taskId, string $columnId, int $newPosition): KanbanTask
    {
        $task = KanbanTask::findOrFail($taskId);
        $task->update([
            'column_id' => $columnId,
            'position_order' => $newPosition,
        ]);

        return $task;
    }
}
