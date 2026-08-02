<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRenewal extends Model
{
    use HasFactory;

    protected $table = 'contract_renewals';

    protected $fillable = [
        'contract_id',
        'renewal_date',
        'new_value',
        'notes',
    ];

    protected $casts = [
        'renewal_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(LegalContract::class, 'contract_id');
    }
}
