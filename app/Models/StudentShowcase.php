<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentShowcase extends Model
{
    use HasFactory;

    /** Roles that count as a student (student_id points at users). */
    public const STUDENT_ROLES = ['student', 'siswa'];

    protected $fillable = [
        'student_id', 'project_title', 'description', 'cover_image_url', 'media_urls', 'is_public', 'view_count',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'is_public' => 'boolean',
        'view_count' => 'integer',
    ];

    /** The student's user account (student_id references users). */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function endorsements()
    {
        return $this->hasMany(ShowcaseEndorsement::class, 'showcase_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && (int) $this->student_id === (int) $user->id;
    }

    /**
     * cover_image_url holds either a path on the public disk (uploads) or an
     * absolute URL; this always returns something an <img> can load.
     */
    public function getCoverUrlAttribute(): ?string
    {
        $value = $this->cover_image_url;
        if (blank($value)) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://', '/'])
            ? $value
            : Storage::disk('public')->url($value);
    }

    /** True when the cover is a file this app stored (and may delete). */
    public function hasStoredCover(): bool
    {
        return filled($this->cover_image_url)
            && ! Str::startsWith($this->cover_image_url, ['http://', 'https://', '/']);
    }
}
