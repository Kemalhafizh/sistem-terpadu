<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'position',
        'employment_status',
        'hire_date',
        'base_salary',
        'hourly_rate',
        'bank_name',
        'bank_account',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
    ];

    /**
     * Get the user associated with the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attendances for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    /**
     * Get the leave requests for the employee.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the employee's name from the user relationship.
     */
    public function getNameAttribute()
    {
        return $this->user?->name;
    }

    /**
     * Get the employee's email from the user relationship.
     */
    public function getEmailAttribute()
    {
        return $this->user?->email;
    }
}
