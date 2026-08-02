<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnershipActivity extends Model
{
    use HasFactory;

    protected $table = 'partnership_activities';

    protected $fillable = [
        'partnership_id',
        'title',
        'description',
        'event_date',
        'participant_count',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function partnership(): BelongsTo
    {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }
}
