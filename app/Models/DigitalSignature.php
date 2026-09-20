<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_id',
        'signature_data',
        'ip_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
