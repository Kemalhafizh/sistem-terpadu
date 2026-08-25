<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KanbanTask extends Model
{
    use HasFactory;

    protected $table = 'kanban_tasks';

    protected $fillable = [
        'kanban_board_id',
        'title',
        'description',
        'column_id',
        'position_order',
        'due_date',
        'priority',
        'assignee_id',
    ];

    protected $casts = [
        'position_order' => 'integer',
        'due_date' => 'date',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(KanbanBoard::class, 'kanban_board_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }
}
