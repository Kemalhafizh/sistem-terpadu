<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'default_amount', 'is_monthly'];

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }
}
