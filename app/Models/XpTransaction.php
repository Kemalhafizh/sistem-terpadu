<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpTransaction extends Model
{
    use HasFactory;

    protected $table = 'xp_transactions';

    protected $fillable = [
        'user_id',
        'xp_amount',
        'source',
        'description',
        'earned_date',
    ];

    protected $casts = [
        'xp_amount' => 'integer',
        'earned_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
