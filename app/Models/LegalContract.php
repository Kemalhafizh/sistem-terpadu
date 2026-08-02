<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalContract extends Model
{
    use HasFactory;

    protected $table = 'legal_contracts';

    protected $fillable = [
        'contract_number',
        'title',
        'vendor_name',
        'contract_value',
        'start_date',
        'end_date',
        'status',
        'document_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function renewals(): HasMany
    {
        return $this->hasMany(ContractRenewal::class, 'contract_id');
    }
}
