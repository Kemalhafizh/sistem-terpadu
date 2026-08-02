<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DapodikExport extends Model
{
    use HasFactory;

    protected $table = 'dapodik_exports';

    protected $fillable = [
        'academic_year',
        'semester',
        'export_file_path',
        'status',
        'records_processed',
        'error_count',
        'exported_by',
    ];

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }

    public function syncErrors(): HasMany
    {
        return $this->hasMany(DapodikSyncError::class, 'export_id');
    }
}
