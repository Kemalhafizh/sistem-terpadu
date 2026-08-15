<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchandiseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'student_id',
        'total_amount',
        'payment_status',
        'fulfillment_status',
        'order_items',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'order_items' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
