<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Project extends Model
{
    use HasUuid, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    /**
     * Official Kemendikdasmen Kurikulum Merdeka SMA/MA P5 project themes.
     *
     * @var array<string, string>
     */
    public const THEMES = [
        'gaya_hidup_berkelanjutan' => 'Gaya Hidup Berkelanjutan',
        'kearifan_lokal' => 'Kearifan Lokal',
        'bhinneka_tunggal_ika' => 'Bhinneka Tunggal Ika',
        'bangunlah_jiwa_dan_raganya' => 'Bangunlah Jiwa dan Raganya',
        'suara_demokrasi' => 'Suara Demokrasi',
        'rekayasa_dan_teknologi' => 'Rekayasa dan Teknologi',
        'kewirausahaan' => 'Kewirausahaan',
        'kebekerjaan' => 'Kebekerjaan',
    ];

    protected $fillable = [
        'school_year_id',
        'academic_year_id',
        'grade_level',
        'phase',
        'theme',
        'title',
        'description',
        'coordinator_id',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    public static function inferPhaseForGrade(string $gradeLevel): string
    {
        return strtoupper(trim($gradeLevel)) === 'X' ? 'E' : 'F';
    }

    public function getThemeLabelAttribute(): string
    {
        return self::THEMES[$this->theme] ?? ucwords(str_replace('_', ' ', (string) $this->theme));
    }

    /** @return BelongsTo<SchoolYear, $this> */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /** @return BelongsTo<User, $this> */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /** @return HasMany<P5ProjectDimension, $this> */
    public function dimensions(): HasMany
    {
        return $this->hasMany(P5ProjectDimension::class, 'p5_project_id')->orderBy('display_order')->orderBy('id');
    }

    /** @return HasMany<P5ProjectFacilitator, $this> */
    public function facilitators(): HasMany
    {
        return $this->hasMany(P5ProjectFacilitator::class, 'p5_project_id');
    }

    /** @return HasMany<P5ProjectAssessment, $this> */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5ProjectAssessment::class, 'p5_project_id');
    }

    /** @return HasMany<P5ProjectStudentNote, $this> */
    public function studentNotes(): HasMany
    {
        return $this->hasMany(P5ProjectStudentNote::class, 'p5_project_id');
    }
}
