<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'advisor_id',
        'schedule_day',
        'schedule_time',
        'location',
        'max_members',
        'is_active',
        'cover_image',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_members' => 'integer',
    ];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function members()
    {
        return $this->hasMany(ExtracurricularMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(ExtracurricularMember::class)->where('status', 'active');
    }

    public function attendances()
    {
        return $this->hasMany(ExtracurricularAttendance::class);
    }

    public function activities()
    {
        return $this->hasMany(ExtracurricularActivity::class)->orderBy('activity_date', 'desc');
    }

    public function getAvailableSlotsAttribute(): int
    {
        if (is_null($this->max_members)) {
            return 9999; // Unlimited
        }
        return max(0, $this->max_members - $this->activeMembers()->count());
    }

    public function achievements()
    {
        return $this->hasMany(ExtracurricularAchievement::class);
    }
}
