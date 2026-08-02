<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $table = 'maintenance_logs';

    protected $fillable = [
        'asset_id',
        'technician_id',
        'service_type',
        'findings',
        'cost',
        'service_date',
        'next_service_due',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_due' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FacilityAsset::class, 'asset_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
