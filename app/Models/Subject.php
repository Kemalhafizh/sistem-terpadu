<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
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
