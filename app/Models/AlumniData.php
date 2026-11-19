<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AlumniData extends Model
{
    use HasUuid;

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

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return HasOne<TracerResponse, $this> */
    public function tracerResponse(): HasOne
    {
        return $this->hasOne(TracerResponse::class, 'alumni_data_id');
    }
}
