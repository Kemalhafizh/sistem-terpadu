<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffRotation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'from_unit_id', 'to_unit_id', 'rotation_type', 'effective_date', 'end_date', 'reason', 'status', 'approved_by'];

    protected $casts = ['effective_date' => 'date', 'end_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(FoundationUnit::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(FoundationUnit::class, 'to_unit_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
