<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapodikSyncError extends Model
{
    use HasFactory;

    protected $table = 'dapodik_sync_errors';

    protected $fillable = [
        'export_id',
        'entity_type',
        'entity_identifier',
        'error_message',
    ];

    public function export(): BelongsTo
    {
        return $this->belongsTo(DapodikExport::class, 'export_id');
    }
}
