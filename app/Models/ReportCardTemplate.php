<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReportCardTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public const REPORT_TYPE_PTS = 'pts';

    public const REPORT_TYPE_PAS = 'pas';

    public const STATUS_NILAI_DISUSUN = 'nilai_disusun';

    public const STATUS_NILAI_LENGKAP = 'nilai_lengkap';

    public const STATUS_DIEKSPOR = 'diekspor';

    public const STATUS_RAPOR_RESMI_DIUNGGAH = 'rapor_resmi_diunggah';

    public const STATUS_TERBIT = 'terbit';

    protected $guarded = [];

    protected $casts = [
        'header_config' => 'array',
        'signature_config' => 'array',
        'grade_descriptors' => 'array',
        'character_dimensions' => 'array',
        'is_published' => 'boolean',
        'semester_number' => 'integer',
        'issued_date' => 'date',
        'published_at' => 'datetime',
    ];

    /** @return BelongsTo<AcademicYear, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function entries()
    {
        return $this->hasMany(ReportCardEntry::class, 'report_card_id');
    }

    public function characters()
    {
        return $this->hasMany(ReportCardCharacter::class, 'report_card_id');
    }

    public function pdfs()
    {
        return $this->hasMany(ReportCardPdf::class, 'report_card_id');
    }
}
