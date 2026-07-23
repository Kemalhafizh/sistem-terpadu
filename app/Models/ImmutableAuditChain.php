<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImmutableAuditChain extends Model
{
    use HasFactory;

    protected $table = 'immutable_audit_chains';

    protected $fillable = [
        'sequence',
        'user_id',
        'action',
        'payload',
        'previous_hash',
        'entry_hash',
        'ip_address',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
