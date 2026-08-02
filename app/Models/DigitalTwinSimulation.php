<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalTwinSimulation extends Model
{
    use HasFactory;

    protected $table = 'digital_twin_simulations';

    protected $fillable = [
        'scenario_name',
        'parameters',
        'predicted_outcomes',
        'created_by',
    ];

    protected $casts = [
        'predicted_outcomes' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
