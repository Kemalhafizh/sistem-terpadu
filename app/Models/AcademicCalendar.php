<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicCalendar extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draf';

    public const STATUS_APPROVED = 'disahkan';

    public const STATUS_PUBLISHED = 'terbit';

    public const MILESTONES = [
        'ta_mulai' => 'Awal Tahun Ajaran',
        'ta_selesai' => 'Akhir Tahun Ajaran',
        'semester_mulai' => 'Awal Semester',
        'semester_selesai' => 'Akhir Semester',
        'pts' => 'Penilaian Tengah Semester (PTS)',
        'penilaian_akhir_semester' => 'Penilaian Akhir Semester',
        'penilaian_akhir_tahun' => 'Penilaian Akhir Tahun',
        'batas_input_nilai' => 'Batas Akhir Input Nilai Rapor',
        'pembagian_rapor' => 'Pembagian Rapor',
        'survei_peminatan' => 'Jendela Survei Peminatan Kelas X',
        'rapat_kenaikan' => 'Rapat Dewan Guru Kenaikan Kelas',
        'kelulusan' => 'Pengesahan & Pengumuman Kelulusan',
        'mpls' => 'Masa Pengenalan Lingkungan Sekolah (MPLS)',
        'spmb' => 'Periode Penerimaan Siswa Baru (SPMB)',
    ];

    protected $fillable = [
        'school_year_id',
        'status',
        'needs_review',
        'approved_by',
        'approved_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'needs_review' => 'boolean',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<AcademicEvent, $this> */
    public function events(): HasMany
    {
        return $this->hasMany(AcademicEvent::class, 'academic_calendar_id');
    }

    /**
     * Look up a milestone event in this calendar without guessing dates.
     */
    public function milestoneEvent(string $milestoneCode, ?int $academicYearId = null): ?AcademicEvent
    {
        return $this->events()
            ->where('milestone', $milestoneCode)
            ->when($academicYearId !== null, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->orderBy('start_date')
            ->first();
    }
}
