<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CpdTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'academic_year',
        'target_credits',
        'achieved_credits',
        'status',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
