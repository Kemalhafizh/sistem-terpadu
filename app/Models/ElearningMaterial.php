<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ElearningMaterial extends Model
{
    use HasUuid;

    protected $fillable = ['uuid', 'elearning_class_id', 'title', 'description', 'file_path', 'video_url'];

    public function elearningClass()
    {
        return $this->belongsTo(ElearningClass::class, 'elearning_class_id');
    }

    public function comments()
    {
        return $this->hasMany(ElearningMaterialComment::class, 'elearning_material_id');
    }

    public function views()
    {
        return $this->hasMany(ElearningMaterialView::class, 'elearning_material_id');
    }

    public function isViewedBy(Student $student)
    {
        return $this->views()->where('student_id', $student->id)->exists();
    }
}
