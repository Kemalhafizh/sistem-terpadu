<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'credential_type',
        'certificate_number',
        'recipient_name',
        'merkle_root',
        'transaction_hash',
        'contract_address',
        'block_number',
        'issued_at',
        'revoked_at',
        'metadata_json',
    ];

    protected $casts = [
        'block_number' => 'integer',
        'issued_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
