<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniData extends Model
{
    use \App\Traits\HasUuid;

    protected $fillable = [
        'student_id',
        'graduation_year',
        'current_activity',
        'institution_name',
        'contact_info',
        'phone_number',
        'email',
        'city',
        'province',
        'country',
        'linkedin_url',
        'instagram_url',
        'current_position',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tracerResponse()
    {
        return $this->hasOne(TracerResponse::class, 'alumni_data_id');
    }
}
