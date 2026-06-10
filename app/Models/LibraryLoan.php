<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class LibraryLoan extends Model
{
    use \App\Traits\LogsActivity, HasUuid;

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
}
