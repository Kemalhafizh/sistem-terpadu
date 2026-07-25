<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'standard_name',
        'category',
        'description',
        'weight',
    ];

    protected $casts = [
        'weight' => 'float',
    ];

    public function indicators(): HasMany
    {
        return $this->hasMany(AccreditationIndicator::class, 'accreditation_standard_id');
    }
}
