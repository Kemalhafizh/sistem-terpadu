<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

        static::saved(function ($maintenance) {
            $asset = $maintenance->asset;
            if ($maintenance->status === 'in_progress') {
                $asset->update(['status' => 'repairing']);
            } elseif ($maintenance->status === 'completed') {
                $asset->update(['status' => 'available']);
            }
        });
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
