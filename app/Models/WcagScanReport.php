<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WcagScanReport extends Model
{
    use HasFactory;

    protected $table = 'wcag_scan_reports';

    protected $fillable = [
        'scanned_url',
        'compliance_score',
        'total_issues',
        'critical_issues',
        'scanned_at',
    ];

    protected $casts = [
        'compliance_score' => 'decimal:2',
        'total_issues' => 'integer',
        'critical_issues' => 'integer',
        'scanned_at' => 'datetime',
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(WcagIssue::class);
    }
}
