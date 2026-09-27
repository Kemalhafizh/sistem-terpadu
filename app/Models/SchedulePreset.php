<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchedulePreset extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function times(): HasMany
    {
        return $this->hasMany(ScheduleTime::class)->orderBy('session_number');
    }
}
