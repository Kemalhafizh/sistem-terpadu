<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniData extends Model
{
    protected $fillable = ['student_id', 'graduation_year', 'current_activity', 'institution_name', 'contact_info'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tracerResponse()
    {
        return $this->hasOne(TracerResponse::class, 'alumni_data_id');
    }
}
