<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UksMedicineRestock extends Model
{
    use HasFactory;

    protected $fillable = [
        'uks_medicine_id',
        'quantity',
        'restocked_by',
        'notes',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(UksMedicine::class, 'uks_medicine_id');
    }

    public function restocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restocked_by');
    }
}
