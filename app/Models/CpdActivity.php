<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpdActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'cpd_category_id',
        'title',
        'description',
        'provider',
        'start_date',
        'end_date',
        'hours',
        'credits_earned',
        'certificate_path',
        'status',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CpdCategory::class, 'cpd_category_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
