<?php

namespace App\Models;

use App\Services\AccountingService;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

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

        static::saving(function ($billing) {
            if ($billing->exists && $billing->isDirty('remaining_balance') && ! $billing->isDirty('snap_token')) {
                $billing->snap_token = null;
                $billing->payment_url = null;
            }
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

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<PaymentCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
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
