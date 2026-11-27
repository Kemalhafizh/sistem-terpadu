<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id',
        'maintenance_date',
        'next_maintenance_date',
        'type',
        'description',
        'cost',
        'vendor',
        'status',
        'handled_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function (self $maintenance) {
            $maintenance->loadMissing('asset');
            $asset = $maintenance->asset;
            if (! $asset) {
                return;
            }

            if ($maintenance->status === 'in_progress') {
                $asset->update(['status' => 'repairing']);
            } elseif ($maintenance->status === 'completed' && $asset->status !== 'lost') {
                $asset->update(['status' => 'available']);
            }
        });
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<User, $this> */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
