<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolBus extends Model
{
    use HasFactory;

    protected $table = 'school_buses';

    protected $fillable = [
        'plate_number',
        'driver_name',
        'driver_phone',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function routes(): HasMany
    {
        return $this->hasMany(BusRoute::class, 'bus_id');
    }
}
