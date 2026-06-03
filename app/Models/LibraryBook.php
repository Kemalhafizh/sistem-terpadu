<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    use \App\Traits\LogsActivity, HasUuid;

    protected $fillable = [
        'uuid',
        'title',
        'author',
        'category_id',
        'subject_id',
        'type',
        'file_path',
        'cover_path',
        'video_url',
        'is_physical_available',
        'download_count',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_physical_available' => 'boolean',
    ];

    public function category()
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

        // Buku fisik bisa dipinjam jika kolom is_physical_available bernilai true
        // DAN tidak ada data peminjaman aktif (belum dikembalikan)
        return $this->is_physical_available && ! $this->loans()->active()->exists();
    }
}
