<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ElearningAnnouncement extends Model
{
    use HasUuid;

    protected $fillable = ['uuid', 'elearning_class_id', 'user_id', 'content'];

    public function elearningClass()
    {
        return $this->belongsTo(ElearningClass::class, 'elearning_class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
