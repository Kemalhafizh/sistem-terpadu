<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IslamicEvent extends Model {
    use HasFactory;
    protected $fillable = ['event_name', 'event_type', 'gregorian_start_date', 'gregorian_end_date', 'hijri_start_date', 'hijri_end_date', 'description', 'is_school_holiday', 'affected_academic_schedules'];
    protected $casts = ['gregorian_start_date' => 'date', 'gregorian_end_date' => 'date', 'is_school_holiday' => 'boolean', 'affected_academic_schedules' => 'array'];
}