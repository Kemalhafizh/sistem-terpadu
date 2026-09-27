<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use \App\Traits\LogsActivity, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'ticket_number',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'reporter_id',
        'assignee_id',
        'attachment_path',
        'resolution',
        'resolved_at',
        'closed_at',
        'due_date',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Generate sequential ticket number in the format TK-YYYY-XXXX
            if (empty($ticket->ticket_number)) {
                $year = date('Y');
                $prefix = "TK-{$year}-";

                $lastTicket = static::where('ticket_number', 'like', $prefix.'%')
                    ->orderBy('ticket_number', 'desc')
                    ->first();

                if ($lastTicket) {
                    $lastNum = intval(substr($lastTicket->ticket_number, strlen($prefix)));
                    $newNum = $lastNum + 1;
                } else {
                    $newNum = 1;
                }

                $ticket->ticket_number = $prefix.str_pad($newNum, 4, '0', STR_PAD_LEFT);
            }

            // Calculate dynamic SLA due date based on priority
            if (empty($ticket->due_date)) {
                $hours = match ($ticket->priority) {
                    'critical' => 4,
                    'high' => 24,
                    'medium' => 48,
                    'low' => 72,
                    default => 48,
                };
                $ticket->due_date = now()->addHours($hours);
            }
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    // Scopes for status filtering
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting']);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed']);
    }

    public function scopeForUser($query, User $user)
    {
        if (in_array($user->role, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin'])) {
            return $query;
        }

        return $query->where('reporter_id', $user->id);
    }

    // Determine if the ticket has breached its SLA
    public function isOverdue(): bool
    {
        if (in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }

        return $this->due_date && $this->due_date->isPast();
    }
}
