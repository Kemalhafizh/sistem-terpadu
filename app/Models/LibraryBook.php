<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryBook extends Model
{
    use HasFactory, HasUuid, LogsActivity;

    protected $fillable = [
        'uuid',
        'title',
        'author',
        'isbn',
        'category_id',
        'subject_id',
        'type',
        'barcode',
        'nfc_uid',
        'file_path',
        'cover_path',
        'shelf_location',
        'video_url',
        'is_physical_available',
        'total_copies',
        'available_copies',
        'rating',
        'download_count',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_physical_available' => 'boolean',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'rating' => 'float',
    ];

    public function scopeByCode($query, string $code)
    {
        return $query->where('barcode', $code)
            ->orWhere('nfc_uid', $code)
            ->orWhere('uuid', $code)
            ->orWhere('id', $code)
            ->orWhere('isbn', $code);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LibraryCategory::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loans()
    {
        return $this->hasMany(LibraryLoan::class, 'library_book_id');
    }

    public function isAvailableForBorrow(): bool
    {
        if ($this->type !== 'physical') {
            return false;
        }

        if (! $this->is_physical_available) {
            return false;
        }

        if ($this->available_copies !== null && $this->available_copies <= 0) {
            return false;
        }

        $activeLoansCount = $this->loans()->active()->count();
        $total = $this->total_copies ?? 1;

        return $activeLoansCount < $total;
    }

    /**
     * Map model to Android BookItem schema
     */
    public function toMobileItemArray(): array
    {
        $available = $this->isAvailableForBorrow();
        $availCopies = $this->available_copies ?? ($available ? 1 : 0);
        $totCopies = $this->total_copies ?? max(1, $availCopies);

        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'author' => (string) ($this->author ?? 'SMA Islam Sultan Agung 1'),
            'isbn' => (string) ($this->isbn ?? $this->barcode ?? '-'),
            'category' => (string) ($this->category ? $this->category->name : 'Umum'),
            'shelf_location' => (string) ($this->shelf_location ?? 'Koleksi Perpustakaan'),
            'synopsis' => (string) ($this->description ?? 'Tidak ada sinopsis tersedia.'),
            'available_copies' => (int) max(0, $availCopies),
            'total_copies' => (int) max(1, $totCopies),
            'rating' => (float) ($this->rating ?? 4.8),
            'is_available' => (bool) $available,
        ];
    }
}
