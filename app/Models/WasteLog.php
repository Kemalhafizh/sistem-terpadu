<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_date',
        'waste_category',
        'weight_kg',
        'disposal_method',
        'revenue_generated',
        'recorded_by'
    ];

    protected $casts = [
        'log_date' => 'date',
        'weight_kg' => 'decimal:2',
        'revenue_generated' => 'decimal:2',
    ];

    public function recorder()
    {
        return $this->belongsTo(Employee::class, 'recorded_by');
    }
}
