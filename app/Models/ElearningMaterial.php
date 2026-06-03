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
}
