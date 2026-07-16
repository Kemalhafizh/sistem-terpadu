<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Registration extends Model
{
    use \App\Traits\HasUuid, \App\Traits\LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'full_name',
        'email',
        'nisn',
        'previous_school',
        'parent_phone',
        'document_kk',
        'document_certificate',
        'status',
        'academic_year_id',
        'user_id',
        'spmb_wave_id',
        'spmb_track_id',
        'gender',
        'birth_date',
        'birth_place',
        'address',
        'parent_name',
        'photo_path',
        'document_skhun_path',
        'document_akta_path',
        'rapor_scores',
        'achievement_data',
        'tahfidz_data',
        'entrance_test_score',
        'total_score',
        'rank',
        'document_status',
        'admin_notes',
        'has_paid_registration',
        'has_reregistered',
    ];

    protected $casts = [
        'rapor_scores' => 'array',
        'achievement_data' => 'array',
        'tahfidz_data' => 'array',
        'birth_date' => 'date',
        'has_paid_registration' => 'boolean',
        'has_reregistered' => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function spmbWave()
    {
        return $this->belongsTo(SpmbWave::class, 'spmb_wave_id');
    }

    public function spmbTrack()
    {
        return $this->belongsTo(SpmbTrack::class, 'spmb_track_id');
    }

    public function spmbDocuments()
    {
        return $this->hasMany(SpmbDocument::class, 'registration_id');
    }

    // Generator Nomor Pendaftaran Otomatis
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });
        static::deleted(function () {
            Cache::forget('admin_dashboard_stats_v2');
        });
    }

    public static function generateRegistrationNumber()
    {
        $year = date('Y');
        $lastRegistration = self::whereYear('created_at', $year)->latest('id')->first();

        if ($lastRegistration) {
            // Ambil 4 digit terakhir
            $lastNumber = (int) substr($lastRegistration->registration_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "SPMB-{$year}-{$newNumber}";
    }
}
