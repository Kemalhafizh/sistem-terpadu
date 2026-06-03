<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulePreset extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function times()
    {
        return $this->hasMany(ScheduleTime::class)->orderBy('session_number');
    }
}
