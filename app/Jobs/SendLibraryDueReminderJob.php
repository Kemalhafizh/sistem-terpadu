<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\LibraryLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendLibraryDueReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $loanId
    ) {}

    public function handle(): void
    {
        $loan = LibraryLoan::with(['student.user', 'book'])->find($this->loanId);

        if (! $loan) {
            Log::warning("SendLibraryDueReminderJob: Peminjaman ID {$this->loanId} tidak ditemukan.");

            return;
        }

        // Jika buku sudah dikembalikan, batalkan pengingat
        if ($loan->returned_at !== null) {
            Log::info("SendLibraryDueReminderJob: Buku {$loan->book->title} sudah dikembalikan oleh siswa ID {$loan->student_id}. Pengingat dibatalkan.");

            return;
        }

        $student = $loan->student;
        $user = $student?->user;
        $bookTitle = $loan->book?->title ?? 'Buku Perpustakaan';
        $dueDate = $loan->due_at ? $loan->due_at->format('d-m-Y') : 'besok';

        $title = '⚠️ Pengingat Jatuh Tempo Perpustakaan (H-1)';
        $body = "Buku \"{$bookTitle}\" jatuh tempo besok ({$dueDate}). Mohon segera kembalikan ke perpustakaan untuk menghindari denda.";

        Log::info("SendLibraryDueReminderJob: Mengirim notifikasi H-1 ke siswa ID {$loan->student_id} untuk buku {$bookTitle}.");

        if ($user) {
            SendFcmNotificationJob::dispatch(
                [$user->id],
                $title,
                $body,
                [
                    'type' => 'library_due_reminder',
                    'loan_id' => (string) $loan->id,
                    'book_id' => (string) $loan->library_book_id,
                ],
                'channel_library'
            );
        }
    }
}
