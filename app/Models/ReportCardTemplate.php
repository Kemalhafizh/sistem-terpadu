<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected $guarded = [];

    protected $casts = [
        'header_config' => 'array',
        'signature_config' => 'array',
        'grade_descriptors' => 'array',
        'character_dimensions' => 'array',
        'is_published' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
