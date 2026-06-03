<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'location_lat',
        'location_long',
        'distance_meters',
        'photo_path',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the employee associated with the attendance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
