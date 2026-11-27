<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;

class Asset extends Model
{
    use HasUuid, LogsActivity;

    protected $fillable = [
        'uuid',
        'asset_code',
        'name',
        'category_id',
        'room_id',
        'purchase_date',
        'purchase_price',
        'useful_life_years',
        'is_borrowable',
        'qr_code',
        'condition',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'useful_life_years' => 'integer',
        'is_borrowable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Autogenerate QR Code link pointing to QR scan / damage report page on creation
        static::created(function ($asset) {
            if (! $asset->qr_code) {
                $targetRoute = Route::has('assets.scan')
                    ? route('assets.scan', $asset->uuid)
                    : route('assets.show', $asset->uuid);
                $asset->update([
                    'qr_code' => $targetRoute,
                ]);
            }
        });
    }

    // Straight-Line Depreciation Dynamic Calculation Accessor
    public function getCurrentValueAttribute()
    {
        if (! $this->purchase_price || ! $this->useful_life_years || ! $this->purchase_date) {
            return $this->purchase_price ?? 0;
        }

        $ageDays = $this->purchase_date->diffInDays(now(), false);
        if ($ageDays <= 0) {
            return $this->purchase_price;
        }

        $totalUsefulDays = $this->useful_life_years * 365;
        if ($ageDays >= $totalUsefulDays) {
            return 0.00;
        }

        $depreciationPerDay = $this->purchase_price / $totalUsefulDays;
        $accumulatedDepreciation = $depreciationPerDay * $ageDays;

        return max(0.00, round($this->purchase_price - $accumulatedDepreciation, 2));
    }

    /** @return BelongsTo<AssetCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<AssetLoan, $this> */
    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }

    /** @return HasMany<AssetMaintenance, $this> */
    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    /** @return HasMany<AssetMaintenanceTicket, $this> */
    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(AssetMaintenanceTicket::class);
    }
}
