<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StrategicPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'vision',
        'mission',
        'start_year',
        'end_year',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function objectives(): HasMany
    {
        return $this->hasMany(StrategicObjective::class);
    }
}
