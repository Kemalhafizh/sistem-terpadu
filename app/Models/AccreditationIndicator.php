<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'accreditation_standard_id',
        'indicator_code',
        'indicator_name',
        'description',
        'data_source',
        'auto_query',
        'target_value',
    ];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(AccreditationStandard::class, 'accreditation_standard_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AccreditationEvidence::class, 'accreditation_indicator_id');
    }
}
