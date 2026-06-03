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
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
