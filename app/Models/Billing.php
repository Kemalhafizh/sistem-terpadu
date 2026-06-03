<?php

namespace App\Models;

use App\Services\AccountingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use \App\Traits\LogsActivity, HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($billing) {
            if (! $billing->remaining_balance) {
                $billing->remaining_balance = $billing->amount;
            }
        });

        static::created(function ($billing) {
            AccountingService::recordBillingRelease($billing);
        });
    }

    protected $fillable = [
        'student_id',
        'payment_category_id',
        'academic_year_id',
        'month',
        'amount',
        'remaining_balance',
        'due_date',
        'status',
        'snap_token',
        'payment_url',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Update status based on payments.
     */
    public function updateStatus()
    {
        $paid = $this->payments()->sum('amount_paid');

        if ($paid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($paid > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'unpaid';
        }

        $this->remaining_balance = max(0, $this->amount - $paid);

        $this->save();
    }
}
