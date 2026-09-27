<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoryCard extends Model
{
    use HasFactory;

    protected $fillable = ['subject_id', 'front_content', 'back_content', 'difficulty_level'];

    public function logs()
    {
        return $this->hasMany(SpacedRepetitionLog::class);
    }
}
