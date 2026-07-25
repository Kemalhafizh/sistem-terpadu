<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationEvidence extends Model
{
    use HasFactory;

    protected $table = 'accreditation_evidences';

    protected $fillable = [
        'accreditation_indicator_id',
        'evidence_type',
        'file_path',
        'description',
        'academic_year',
        'uploaded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AccreditationIndicator::class, 'accreditation_indicator_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
