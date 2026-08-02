<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusRoute extends Model
{
    use HasFactory;

    protected $table = 'bus_routes';

    protected $fillable = [
        'bus_id',
        'route_name',
        'waypoints',
        'departure_time',
        'arrival_time',
    ];

    protected $casts = [
        'waypoints' => 'array',
    ];

    public function bus(): BelongsTo
    {
        return $this->belongsTo(SchoolBus::class, 'bus_id');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BusPassenger::class, 'route_id');
    }
}
