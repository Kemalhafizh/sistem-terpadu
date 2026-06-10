<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElearningMaterialView extends Model
{
    protected $fillable = ['student_id', 'elearning_material_id', 'viewed_at'];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function material()
    {
        return $this->belongsTo(ElearningMaterial::class, 'elearning_material_id');
    }
}
