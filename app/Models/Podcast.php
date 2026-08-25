<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    use HasFactory;

    protected $table = 'podcasts';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image_path',
        'category',
        'host_id',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class)->orderBy('published_at', 'desc');
    }
}
