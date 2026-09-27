<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BankTransaction extends Model {
    use HasFactory;
    protected $fillable = ['bank_statement_id', 'transaction_date', 'description', 'reference_number', 'debit', 'credit', 'balance', 'match_status', 'matched_billing_id', 'matched_payment_id', 'matched_by', 'match_notes'];
    protected $casts = ['transaction_date' => 'date', 'debit' => 'decimal:2', 'credit' => 'decimal:2', 'balance' => 'decimal:2'];
    public function statement() { return $this->belongsTo(BankStatement::class, 'bank_statement_id'); }
    public function matchedBilling() { return $this->belongsTo(Billing::class, 'matched_billing_id'); }
    public function matchedPayment() { return $this->belongsTo(Payment::class, 'matched_payment_id'); }
}