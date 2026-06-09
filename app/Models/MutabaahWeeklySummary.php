<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutabaahWeeklySummary extends Model
{
    protected $fillable = [
        'student_id',
        'week_start',
        'week_end',
        'total_score',
        'sholat_5_waktu_count',
        'sholat_berjamaah_count',
        'tadarus_total_pages',
        'grade',
        'wali_kelas_comment',
        'parent_acknowledged',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'total_score' => 'integer',
        'sholat_5_waktu_count' => 'integer',
        'sholat_berjamaah_count' => 'integer',
        'tadarus_total_pages' => 'integer',
        'parent_acknowledged' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
