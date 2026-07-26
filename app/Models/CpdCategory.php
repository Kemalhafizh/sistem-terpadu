<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CpdCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'credit_weight',
        'max_credits_per_year',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(CpdActivity::class);
    }
}
