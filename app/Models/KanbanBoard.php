<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanBoard extends Model
{
    use HasFactory;

    protected $table = 'kanban_boards';

    protected $fillable = [
        'title',
        'type',
        'owner_id',
        'columns',
    ];

    protected $casts = [
        'columns' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(KanbanTask::class)->orderBy('position_order');
    }
}
