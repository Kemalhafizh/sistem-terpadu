<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Subject extends Model
{
    use HasFactory;

    public const CATEGORY_UMUM = 'umum';

    public const CATEGORY_PILIHAN = 'pilihan';

    public const CATEGORY_MUATAN_LOKAL = 'muatan_lokal';

    public const CATEGORY_CIRI_KHAS = 'ciri_khas';

    protected $fillable = [
        'name',
        'code',
        'category',
        'display_group',
        'kkm',
        'description',
    ];

    /**
     * Get all subjects, cached forever.
     */
    public static function allCached()
    {
        return Cache::rememberForever('subjects_all', function () {
            return self::orderBy('name')->get();
        });
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('subjects_all');
        });
        static::deleted(function () {
            Cache::forget('subjects_all');
        });
    }
}
