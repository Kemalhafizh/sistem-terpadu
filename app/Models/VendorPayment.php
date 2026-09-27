<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VendorPayment extends Model {
    use HasFactory;

    public const STATUS_LABELS = ['scheduled' => 'Terjadwal', 'overdue' => 'Terlambat', 'paid' => 'Lunas', 'cancelled' => 'Dibatalkan'];
    public const METHOD_LABELS = ['transfer' => 'Transfer', 'cash' => 'Tunai', 'giro' => 'Giro', 'auto_debit' => 'Auto Debit'];

    protected $fillable = ['vendor_name', 'invoice_number', 'description', 'amount', 'due_date', 'paid_date', 'payment_method', 'status', 'bank_account_id', 'proof_file_path', 'created_by', 'approved_by'];
    protected $casts = ['due_date' => 'date', 'paid_date' => 'date', 'amount' => 'decimal:2'];
    public function account() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    /** Nothing flips the stored status to "overdue", so it is derived from the due date. */
    public function effectiveStatus(): string {
        if (in_array($this->status, ['scheduled', 'overdue'], true)) {
            return $this->due_date->lt(today()) ? 'overdue' : 'scheduled';
        }
        return $this->status;
    }

    public function statusLabel(): string {
        return self::STATUS_LABELS[$this->effectiveStatus()] ?? $this->status;
    }

    public function isOpen(): bool {
        return in_array($this->status, ['scheduled', 'overdue'], true);
    }

    public function scopeOpen(Builder $query): Builder {
        return $query->whereIn('status', ['scheduled', 'overdue']);
    }
}
