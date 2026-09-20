<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OsisAnnouncement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'category',
        'content',
        'likes_count',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Map model to Android OsisPostItem schema
     */
    public function toMobileItemArray(): array
    {
        $dateFormatted = $this->published_at
            ? $this->published_at->translatedFormat('d F Y')
            : $this->created_at->translatedFormat('d F Y');

        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'author' => (string) ($this->author ?: 'Pengurus OSIS'),
            'category' => (string) ($this->category ?: 'Umum'),
            'date' => (string) $dateFormatted,
            'content' => (string) $this->content,
            'likes_count' => (int) ($this->likes_count ?? 0),
        ];
    }
}
