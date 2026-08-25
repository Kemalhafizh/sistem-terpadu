<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDid extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'did_uri',
        'public_key',
        'did_document_json',
        'verifiable_credentials_count',
        'status',
    ];

    protected $casts = [
        'did_document_json' => 'array',
        'verifiable_credentials_count' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
