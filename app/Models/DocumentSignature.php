<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'document_id',
        'signer_id',
        'signer_role',
        'signature_hash',
        'signature_image_path',
        'signed_at',
        'qr_verification_code',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }
}
