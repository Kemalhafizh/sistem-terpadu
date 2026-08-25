<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomLayout extends Model
{
    use HasFactory;

    protected $table = 'classroom_layouts';

    protected $fillable = [
        'classroom_id',
        'room_id',
        'layout_name',
        'grid_rows',
        'grid_columns',
        'furniture_positions',
        'is_active',
    ];

    protected $casts = [
        'grid_rows' => 'integer',
        'grid_columns' => 'integer',
        'furniture_positions' => 'array',
        'is_active' => 'boolean',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function seatAssignments(): HasMany
    {
        return $this->hasMany(SeatAssignment::class);
    }
}
