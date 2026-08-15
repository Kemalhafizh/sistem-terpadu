<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'sku',
        'category',
        'price',
        'available_sizes',
        'current_stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'available_sizes' => 'array',
        'is_active' => 'boolean',
    ];
}
