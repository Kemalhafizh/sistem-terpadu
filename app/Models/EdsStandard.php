<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EdsStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'order',
    ];

    protected static function booted()
    {
        static::addGlobalScope('order', function ($builder) {
            $builder->orderBy('order', 'asc')->orderBy('code', 'asc');
        });
    }

    public function indicators()
    {
        return $this->hasMany(EdsIndicator::class, 'standard_id');
    }
}
