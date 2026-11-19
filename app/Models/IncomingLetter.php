<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomingLetter extends Model
{
    use HasFactory, HasUuid, LogsActivity, SoftDeletes;

    public const STATUS_RECEIVED = 'diterima';

    public const STATUS_DISPOSED = 'didisposisikan';

    public const STATUS_FOLLOWED_UP = 'ditindaklanjuti';

    public const STATUS_COMPLETED = 'selesai';

    public const GLOBAL_VIEWER_ROLES = [
        'superadmin',
        'admin',
        'staf_tu',
        'kepala_sekolah',
        'kepsek',
    ];

    protected $fillable = [
        'uuid',
        'agenda_number',
        'reference_number',
        'sender',
        'subject',
        'letter_date',
        'received_date',
        'category',
        'summary',
        'attachment_path',
        'status',
        'created_by',
        'archived_by',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'received_date' => 'date',
            'archived_at' => 'datetime',
        ];
    }

    /** @return HasMany<LetterDisposition, $this> */
    public function dispositions(): HasMany
    {
        return $this->hasMany(LetterDisposition::class)->latest('id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if (array_intersect($user->effectiveRoles(), self::GLOBAL_VIEWER_ROLES) !== []) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhereHas('dispositions', fn (Builder $dq) => $dq->where('to_user_id', $user->id));
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return static::query()
            ->visibleTo($user)
            ->whereKey($this->getKey())
            ->exists();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_RECEIVED => 'Diterima (Menunggu Disposisi)',
            self::STATUS_DISPOSED => 'Didisposisikan',
            self::STATUS_FOLLOWED_UP => 'Ditindaklanjuti',
            self::STATUS_COMPLETED => 'Selesai & Diarsipkan',
            default => strtoupper((string) $this->status),
        };
    }
}
