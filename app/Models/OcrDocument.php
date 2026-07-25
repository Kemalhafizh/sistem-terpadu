<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_file_path',
        'document_type',
        'extracted_data',
        'confidence_score',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'confidence_score' => 'float',
        'processed_at' => 'datetime',
    ];

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
