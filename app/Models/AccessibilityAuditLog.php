<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessibilityAuditLog extends Model
{
    use HasFactory;

    protected $table = 'accessibility_audit_logs';

    protected $fillable = [
        'page_route',
        'audit_type',
        'issues_found',
        'issues_resolved',
        'wcag_level',
        'audited_by',
    ];
}
