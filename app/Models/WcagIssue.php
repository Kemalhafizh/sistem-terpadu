<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WcagIssue extends Model
{
    use HasFactory;

    protected $table = 'wcag_issues';

    protected $fillable = [
        'wcag_scan_report_id',
        'violation_type',
        'dom_element_selector',
        'description',
        'severity',
        'is_resolved',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(WcagScanReport::class, 'wcag_scan_report_id');
    }
}
