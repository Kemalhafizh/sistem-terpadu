<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UksMedicine extends Model
{
    protected $fillable = ['name', 'category', 'stock', 'min_stock', 'expiry_date', 'notes'];

    protected $casts = [
        'expiry_date' => 'date',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    public function visits(): BelongsToMany
    {
        return $this->belongsToMany(UksVisit::class, 'uks_visit_medicines')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Scope obat dengan stok menipis
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= min_stock');
    }

    /**
     * Scope obat yang akan kedaluwarsa dalam N hari
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString());
    }
}
