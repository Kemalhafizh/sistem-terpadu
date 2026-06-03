<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpkAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'osis_transaction_id',
        'status',
        'notes',
        'audited_by',
    ];

    public function osisTransaction()
    {
        return $this->belongsTo(OsisTransaction::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
