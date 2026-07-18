<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssetMaintenanceTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'reported_by',
        'issue_description',
        'priority',
        'status',
        'photo_evidence',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get photo evidence image URL.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_evidence) {
            return Storage::url($this->photo_evidence);
        }
        return null;
    }

    /**
     * Priority color helpers for CSS badges.
     */
    public function getPriorityColorAttribute()
    {
        return match ($this->priority) {
            'critical' => 'bg-rose-500 text-white',
            'high' => 'bg-orange-500 text-white',
            'medium' => 'bg-amber-500 text-white',
            'low' => 'bg-slate-500 text-white',
            default => 'bg-blue-500 text-white',
        };
    }

    /**
     * Status color helpers for CSS badges.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'investigating' => 'bg-purple-100 text-purple-800 border border-purple-200',
            'repairing' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'resolved' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'scrapped' => 'bg-rose-100 text-rose-800 border border-rose-200',
            default => 'bg-slate-100 text-slate-800 border border-slate-200',
        };
    }
}
