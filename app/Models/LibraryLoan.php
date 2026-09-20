<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryLoan extends Model
{
    use \App\Traits\LogsActivity, HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'student_id',
        'library_book_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'fine_amount',
        'fine_paid_at',
        'created_by',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_at' => 'datetime',
        'returned_at' => 'datetime',
        'fine_paid_at' => 'datetime',
        'fine_amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function book()
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk memfilter peminjaman yang sedang aktif (belum dikembalikan).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    /**
     * Scope untuk memfilter peminjaman yang terlambat (overdue).
     */
    public function scopeOverdue($query)
    {
        return $query->whereNull('returned_at')->where('due_at', '<', now());
    }

    /**
     * Hitung nominal denda keterlambatan secara dinamis.
     */
    public function calculateFine(): float
    {
        // Jika sudah dikembalikan tepat waktu atau belum melewati tenggat
        $endDate = $this->returned_at ?? now();
        if ($endDate <= $this->due_at) {
            return 0.0;
        }

        // Hitung selisih hari keterlambatan
        $daysLate = $this->due_at->diffInDays($endDate, false);
        if ($daysLate <= 0) {
            return 0.0;
        }

        // Ambil nominal denda per hari dari pengaturan (default: Rp 1.000)
        $finePerDay = (float) Setting::get('library_fine_per_day', 1000);

        return ceil($daysLate) * $finePerDay;
    }

    /**
     * Get the number of days the loan is overdue.
     */
    public function daysOverdue(): int
    {
        $endDate = $this->returned_at ?? now();
        if ($endDate <= $this->due_at) {
            return 0;
        }

        $daysLate = $this->due_at->diffInDays($endDate, false);
        return $daysLate > 0 ? (int) ceil($daysLate) : 0;
    }

    /**
     * Map model to Android BookLoanItem schema
     */
    public function toMobileItemArray(): array
    {
        $isReturned = $this->returned_at !== null;
        $isOverdue = ! $isReturned && $this->due_at->isPast();

        if ($isReturned) {
            $mobileStatus = 'RETURNED';
            $daysRemaining = 0;
        } elseif ($isOverdue || $this->status === 'overdue') {
            $mobileStatus = 'OVERDUE';
            $daysRemaining = 0;
        } else {
            $mobileStatus = 'ACTIVE';
            $daysRemaining = max(0, (int) ceil(now()->diffInDays($this->due_at, false)));
        }

        $fine = $this->calculateFine();
        if ($this->fine_amount > 0 && $fine <= 0) {
            $fine = (float) $this->fine_amount;
        }

        return [
            'id' => (int) $this->id,
            'book_id' => (int) $this->library_book_id,
            'book_title' => (string) ($this->book ? $this->book->title : 'Buku Perpustakaan'),
            'borrow_date' => $this->borrowed_at ? $this->borrowed_at->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => $this->due_at ? $this->due_at->format('Y-m-d') : now()->addDays(14)->format('Y-m-d'),
            'days_remaining' => (int) $daysRemaining,
            'status' => $mobileStatus,
            'fine_amount' => (int) $fine,
        ];
    }
}

