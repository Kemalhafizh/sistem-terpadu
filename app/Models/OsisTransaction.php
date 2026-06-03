<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OsisTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'amount',
        'description',
        'category',
        'transaction_date',
        'receipt_path',
        'recorded_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function audit()
    {
        return $this->hasOne(MpkAudit::class, 'osis_transaction_id');
    }
}
