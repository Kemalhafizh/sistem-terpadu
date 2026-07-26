<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScholarshipDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_application_id',
        'amount',
        'disbursement_method',
        'reference_number',
        'disbursed_by',
        'disbursed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disbursed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ScholarshipApplication::class, 'scholarship_application_id');
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }
}
