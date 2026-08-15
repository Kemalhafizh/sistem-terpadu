<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
  use \App\Traits\LogsActivity, HasUuid;

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

    // Autogenerate QR Code link pointing to asset's show page on creation
    static::created(function ($asset) {
      if (!$asset->qr_code) {
        $asset->update([
          'qr_code' => route('assets.show', $asset->uuid),
        ]);
      }
    });
  }

  // Straight-Line Depreciation Dynamic Calculation Accessor
  public function getCurrentValueAttribute()
  {
    if (!$this->purchase_price || !$this->useful_life_years || !$this->purchase_date) {
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

  public function category()
  {
    return $this->belongsTo(AssetCategory::class);
  }

  public function room()
  {
    return $this->belongsTo(Room::class);
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function loans()
  {
    return $this->hasMany(AssetLoan::class);
  }

  public function maintenances()
  {
    return $this->hasMany(AssetMaintenance::class);
  }

  public function maintenanceTickets()
  {
    return $this->hasMany(AssetMaintenanceTicket::class);
  }
}
