<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdsAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'indicator_id',
        'score',
        'evidence',
        'document_paths',
        'recommendation',
        'assessed_by',
    ];

    protected $casts = [
        'document_paths' => 'array',
        'score' => 'integer',
    ];

    public function period()
    {
        return $this->belongsTo(EdsPeriod::class, 'period_id');
    }

    public function indicator()
    {
        return $this->belongsTo(EdsIndicator::class, 'indicator_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
