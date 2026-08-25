<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceBiometricProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feature_vector_hash',
        'embedding_vector',
        'confidence_threshold',
        'enrolled_at',
        'status',
    ];

    protected $casts = [
        'embedding_vector' => 'array',
        'confidence_threshold' => 'float',
        'enrolled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
