<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One upload of a fingerprint machine log and what it did. */
class FingerprintImport extends Model
{
    protected $fillable = [
        'original_name', 'imported_by', 'rows_read', 'scans_matched', 'attendances_created', 'attendances_updated',
        'attendances_unchanged', 'leave_days_kept', 'unknown_ids', 'invalid_rows', 'first_date', 'last_date',
    ];

    protected $casts = [
        'unknown_ids' => 'array',
        'invalid_rows' => 'array',
        'first_date' => 'date',
        'last_date' => 'date',
    ];

    /** @return BelongsTo<User, $this> */
    public function importer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
