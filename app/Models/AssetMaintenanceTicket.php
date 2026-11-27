<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;

class AssetMaintenanceTicket extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_INVESTIGATING = 'investigating';

    public const STATUS_REPAIRING = 'repairing';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_SCRAPPED = 'scrapped';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_INVESTIGATING,
        self::STATUS_REPAIRING,
        self::STATUS_RESOLVED,
        self::STATUS_SCRAPPED,
    ];

    /** @var list<string> */
    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    protected $fillable = [
        'uuid',
        'asset_id',
        'reported_by',
        'handled_by',
        'issue_description',
        'priority',
        'status',
        'photo_evidence',
        'photo_disk',
        'resolution_notes',
        'cost',
        'vendor',
        'investigated_at',
        'repair_started_at',
        'resolved_by',
        'resolved_at',
        'asset_maintenance_id',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'investigated_at' => 'datetime',
        'repair_started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /** @return BelongsTo<User, $this> */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** @return BelongsTo<User, $this> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /** @return BelongsTo<AssetMaintenance, $this> */
    public function maintenanceLog(): BelongsTo
    {
        return $this->belongsTo(AssetMaintenance::class, 'asset_maintenance_id');
    }

    /**
     * Authenticated private photo evidence URL (IDOR-checked in controller/service).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_evidence) {
            return null;
        }

        if (Route::has('assets.maintenances.tickets.attachment')) {
            return route('assets.maintenances.tickets.attachment', $this);
        }

        return null;
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'critical' => 'bg-rose-500 text-white',
            'high' => 'bg-orange-500 text-white',
            'medium' => 'bg-amber-500 text-white',
            'low' => 'bg-slate-500 text-white',
            default => 'bg-blue-500 text-white',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-blue-100 text-blue-800 border border-blue-200',
            self::STATUS_INVESTIGATING => 'bg-purple-100 text-purple-800 border border-purple-200',
            self::STATUS_REPAIRING => 'bg-amber-100 text-amber-800 border border-amber-200',
            self::STATUS_RESOLVED => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            self::STATUS_SCRAPPED => 'bg-rose-100 text-rose-800 border border-rose-200',
            default => 'bg-slate-100 text-slate-800 border border-slate-200',
        };
    }
}
