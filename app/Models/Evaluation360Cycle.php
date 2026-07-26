<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation360Cycle extends Model
{
    use HasFactory;

    protected $table = 'evaluation_360_cycles';

    protected $fillable = [
        'title',
        'academic_year',
        'semester',
        'status',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instruments(): HasMany
    {
        return $this->hasMany(Evaluation360Instrument::class, 'cycle_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(Evaluation360Summary::class, 'cycle_id');
    }
}
