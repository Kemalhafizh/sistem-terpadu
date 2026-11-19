<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterDisposition extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    public const STATUS_ASSIGNED = 'didisposisikan';

    public const STATUS_IN_PROGRESS = 'diproses';

    public const STATUS_DONE = 'selesai';

    public const PRIORITIES = ['rendah', 'normal', 'penting', 'segera'];

    protected $fillable = [
        'uuid',
        'incoming_letter_id',
        'from_user_id',
        'to_user_id',
        'instruction',
        'priority',
        'due_at',
        'status',
        'follow_up_notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<IncomingLetter, $this> */
    public function incomingLetter(): BelongsTo
    {
        return $this->belongsTo(IncomingLetter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }
}
