<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
        'asset_id',
        'borrower_id',
        'approved_by',
        'borrowed_at',
        'expected_return_at',
        'returned_at',
        'status',
        'purpose',
        'return_notes',
        'return_condition',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'expected_return_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Model transactional update hooks to update asset status automatically
        static::updated(function ($loan) {
            if ($loan->isDirty('status')) {
                $asset = $loan->asset;
                if (in_array($loan->status, ['approved', 'borrowed'])) {
                    $asset->update(['status' => 'borrowed']);
                } elseif ($loan->status === 'returned') {
                    $asset->update([
                        'status' => 'available',
                        'condition' => $loan->return_condition ?? $asset->condition,
                    ]);
                } elseif ($loan->status === 'lost') {
                    $asset->update(['status' => 'lost']);
                } elseif ($loan->status === 'rejected') {
                    $asset->update(['status' => 'available']);
                }
            }
        });

        static::created(function ($loan) {
            // Sync status on direct creation if it is active (e.g. testing or seeding)
            if (in_array($loan->status, ['approved', 'borrowed'])) {
                $loan->asset->update(['status' => 'borrowed']);
            }
        });
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<User, $this> */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
