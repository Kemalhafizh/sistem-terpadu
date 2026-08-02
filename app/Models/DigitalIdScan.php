<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalIdScan extends Model
{
    use HasFactory;

    protected $table = 'digital_id_scans';

    public $timestamps = false;

    protected $fillable = [
        'card_id',
        'location',
        'scan_type',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(DigitalIdCard::class, 'card_id');
    }
}
