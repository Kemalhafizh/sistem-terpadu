<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class VendorPayment extends Model {
    use HasFactory;
    protected $fillable = ['vendor_name', 'invoice_number', 'description', 'amount', 'due_date', 'paid_date', 'payment_method', 'status', 'bank_account_id', 'proof_file_path', 'created_by', 'approved_by'];
    protected $casts = ['due_date' => 'date', 'paid_date' => 'date', 'amount' => 'decimal:2'];
    public function account() { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
}