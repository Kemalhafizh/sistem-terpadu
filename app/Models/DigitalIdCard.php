<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalIdCard extends Model
{
    use HasFactory;

    protected $table = 'digital_id_cards';

    protected $fillable = [
        'student_id',
        'card_hash',
        'nfc_uid',
        'issue_date',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(DigitalIdScan::class, 'card_id');
    }
}
