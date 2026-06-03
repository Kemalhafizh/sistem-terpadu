<?php

namespace App\Models;

use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'billing_id',
        'created_by',
        'amount_paid',
        'payment_date',
        'payment_method',
        'receipt_number',
        'note',
    ];

    protected static function booted()
    {
        static::created(function ($payment) {
            AccountingService::recordBillingPayment($payment);
        });

        static::saved(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });
        static::deleted(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
