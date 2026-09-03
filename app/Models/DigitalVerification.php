<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'document_type',
        'student_nisn',
        'student_name',
        'issuer_title',
        'issuer_name',
        'sha256_hash',
        'qr_verification_url',
        'issued_date',
        'is_valid',
        'metadata',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'issued_date' => 'date',
        'metadata' => 'array',
    ];
}
