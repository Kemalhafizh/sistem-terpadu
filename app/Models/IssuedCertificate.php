<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedCertificate extends Model
{
    use HasFactory;

    protected $table = 'issued_certificates';

    protected $fillable = [
        'student_id',
        'template_id',
        'verification_code',
        'metadata',
        'pdf_path',
        'issued_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'issued_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }
}
