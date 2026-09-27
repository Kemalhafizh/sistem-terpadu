<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PettyCashTransaction extends Model
{
    use HasFactory;

    public const TYPE_LABELS = ['pengeluaran' => 'Pengeluaran', 'pengisian_ulang' => 'Pengisian Ulang'];

    public const CATEGORY_LABELS = [
        'atk' => 'ATK', 'konsumsi' => 'Konsumsi', 'transportasi' => 'Transportasi', 'fotocopy' => 'Fotokopi',
        'peralatan_kecil' => 'Peralatan Kecil', 'kebersihan' => 'Kebersihan', 'obat_p3k' => 'Obat & P3K', 'lainnya' => 'Lainnya',
    ];

    public const STATUS_LABELS = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];

    protected $fillable = ['fund_id', 'type', 'amount', 'description', 'receipt_path', 'transaction_date', 'category', 'approval_status', 'requested_by', 'approved_by', 'rejection_reason', 'balance_applied_at'];

    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:2', 'balance_applied_at' => 'datetime'];

    public function fund()
    {
        return $this->belongsTo(PettyCashFund::class, 'fund_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approved before PettyCashService existed, so the fund balance never
     * moved — see treasury:apply-legacy-petty-cash.
     */
    public function scopeUnappliedApproved($query)
    {
        return $query->where('approval_status', 'approved')->whereNull('balance_applied_at');
    }
}
