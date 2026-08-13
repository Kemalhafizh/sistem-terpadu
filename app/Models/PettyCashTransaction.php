<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PettyCashTransaction extends Model {
    use HasFactory;
    protected $fillable = ['fund_id', 'type', 'amount', 'description', 'receipt_path', 'transaction_date', 'category', 'approval_status', 'requested_by', 'approved_by', 'rejection_reason'];
    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:2'];
    public function fund() { return $this->belongsTo(PettyCashFund::class, 'fund_id'); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}