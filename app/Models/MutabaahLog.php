<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutabaahLog extends Model
{
    protected $fillable = [
        'student_id',
        'mutabaah_item_id',
        'is_completed',
        'date',
        'verified_by',
        'sholat_subuh',
        'sholat_dzuhur',
        'sholat_ashar',
        'sholat_maghrib',
        'sholat_isya',
        'sholat_dhuha',
        'sholat_tahajud',
        'tadarus_pages',
        'puasa_sunnah',
        'sedekah',
        'dzikir_pagi',
        'dzikir_sore',
        'catatan_harian',
        'score',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'date' => 'date',
        'puasa_sunnah' => 'boolean',
        'sedekah' => 'boolean',
        'dzikir_pagi' => 'boolean',
        'dzikir_sore' => 'boolean',
        'sholat_subuh' => 'integer',
        'sholat_dzuhur' => 'integer',
        'sholat_ashar' => 'integer',
        'sholat_maghrib' => 'integer',
        'sholat_isya' => 'integer',
        'sholat_dhuha' => 'integer',
        'sholat_tahajud' => 'integer',
        'tadarus_pages' => 'integer',
        'score' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($log) {
            if ($log->mutabaah_item_id === null) {
                $log->score = $log->calculateScore();
            }
        });
    }

    public function calculateScore(): int
    {
        $points = 0;

        // 1. Shalat 5 Waktu Wajib
        $sholats = ['sholat_subuh', 'sholat_dzuhur', 'sholat_ashar', 'sholat_maghrib', 'sholat_isya'];
        foreach ($sholats as $sholat) {
            $status = (int) $this->{$sholat};
            if ($status === 2) {
                $points += 15; // Berjamaah
            } elseif ($status === 1) {
                $points += 10; // Sendiri
            }
        }

        // 2. Shalat Sunnah
        if ((int) $this->sholat_dhuha === 1) {
            $points += 5;
        }
        if ((int) $this->sholat_tahajud === 1) {
            $points += 5;
        }

        // 3. Tadarus Al-Quran
        if ((int) $this->tadarus_pages >= 1) {
            $points += 10;
        }

        // 4. Puasa Sunnah
        if ($this->puasa_sunnah) {
            $points += 5;
        }

        // 5. Sedekah
        if ($this->sedekah) {
            $points += 5;
        }

        // 6. Dzikir Pagi & Sore
        if ($this->dzikir_pagi) {
            $points += 2.5;
        }
        if ($this->dzikir_sore) {
            $points += 2.5;
        }

        return min(100, (int) round($points));
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function item()
    {
        return $this->belongsTo(MutabaahItem::class, 'mutabaah_item_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
