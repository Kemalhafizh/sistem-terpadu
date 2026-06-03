<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdsIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_id',
        'code',
        'name',
        'description',
        'order',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('order', 'asc')->orderBy('code', 'asc');
        });
    }

    public function standard()
    {
        return $this->belongsTo(EdsStandard::class, 'standard_id');
    }

    public function assessments()
    {
        return $this->hasMany(EdsAssessment::class, 'indicator_id');
    }
}
