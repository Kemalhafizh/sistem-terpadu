<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'report_type',
        'cron_expression',
        'recipients',
        'delivery_method',
        'attach_pdf',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'attach_pdf' => 'boolean',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active scheduled reports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
