<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    protected $fillable = [
        'employee_id',
        'title',
        'type',
        'organizer',
        'start_date',
        'end_date',
        'hours',
        'certificate_path',
        'certificate_number',
        'certificate_expiry',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'certificate_expiry' => 'date',
        'hours' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
