<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScormPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'version',
        'file_path',
        'manifest_data',
        'entry_point',
        'uploaded_by',
        'status',
    ];

    protected $casts = [
        'manifest_data' => 'array',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(ScormTracking::class);
    }
}
