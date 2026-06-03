<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtracurricularActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'extracurricular_id',
        'title',
        'description',
        'activity_date',
        'gallery_paths',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'gallery_paths' => 'array',
    ];

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
