<?php

namespace App\Models;

use App\Support\Access\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BullyingReport extends Model
{
    use HasFactory;

    /**
     * Who may read the reports (web and API). They carry victims' identities,
     * so this is counselling, student affairs and leadership; anyone signed in
     * may file one.
     */
    public const READER_ROLES = ['bk', 'waka_kesiswaan', ...Roles::PRINCIPAL, 'admin'];

    /** Stable codes the app sends and receives; labels live in lang/{id,en,ar}/bullying.php. */
    public const TYPES = ['verbal', 'fisik', 'cyber', 'sosial_eksklusi', 'pemerasan', 'pelecehan', 'intimidasi', 'lainnya'];

    public const SEVERITIES = ['ringan', 'sedang', 'berat', 'sangat_berat'];

    public const STATUSES = ['reported', 'under_review', 'investigating', 'action_taken', 'closed', 'dismissed'];

    protected $fillable = [
        'ticket_number', 'description', 'type', 'severity', 'location', 'incident_date',
        'is_anonymous', 'reporter_id', 'reporter_alias', 'status', 'assigned_to',
        'investigation_notes', 'action_taken', 'follow_up_plan', 'is_escalated',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'is_anonymous' => 'boolean',
        'is_escalated' => 'boolean',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs()
    {
        return $this->hasMany(BullyingInvestigationLog::class);
    }
}
