<?php

namespace App\Models;

use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PtcSession extends Model
{
    use HasFactory;

    /** Who opens a session: student affairs, leadership and the office. */
    public const ORGANIZER_ROLES = ['waka_kesiswaan', ...Roles::PRINCIPAL, 'staf_tu', 'admin'];

    /** Who gets slots and receives parents: teachers, counsellors and homeroom teachers. */
    public const HOST_ROLES = [...StudentDataAccess::TEACHER_ROLES, 'bk', 'wali_kelas', 'waka_kurikulum', 'waka_kesiswaan'];

    protected $fillable = [
        'title',
        'academic_year',
        'semester',
        'date',
        'start_time',
        'end_time',
        'slot_duration_minutes',
        'location',
        'organizer_id',
        'status',
        'max_slots_per_teacher',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PtcSlot::class);
    }
}
