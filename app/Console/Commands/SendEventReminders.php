<?php

namespace App\Console\Commands;

use App\Models\AcademicEvent;
use App\Models\Student;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'app:send-event-reminders';

    protected $description = 'Kirim pengingat otomatis WhatsApp H-1 untuk acara kalender akademik';

    public function handle()
    {
        $this->info('Memulai pemrosesan pengingat agenda akademik H-1...');

        // Cari acara aktif H-1 (besok hari) dengan opsi send_notification aktif
        $tomorrowStart = now()->addDay()->startOfDay();
        $tomorrowEnd = now()->addDay()->endOfDay();

        $events = AcademicEvent::active()
            ->where('send_notification', true)
            ->whereBetween('start_date', [$tomorrowStart, $tomorrowEnd])
            ->get();

        if ($events->isEmpty()) {
            $this->info('Tidak ada agenda H-1 yang memerlukan pengiriman notifikasi.');

            return 0;
        }

        foreach ($events as $event) {
            $this->info("Memproses notifikasi untuk agenda: {$event->title}");

            // Tentukan target pengiriman notifikasi
            $usersQuery = User::query();

            if ($event->target_role) {
                $usersQuery->where('role', $event->target_role);
            }

            // Jika khusus kelas tertentu
            if ($event->target_classroom_id) {
                // Cari user siswa di kelas tersebut, serta orang tua/wali murid mereka
                $studentUserIds = Student::where('classroom_id', $event->target_classroom_id)
                    ->pluck('user_id');

                $parentUserIds = Student::where('classroom_id', $event->target_classroom_id)
                    ->whereNotNull('parent_id')
                    ->pluck('parent_id');

                if ($event->target_role === 'student') {
                    $usersQuery->whereIn('id', $studentUserIds);
                } elseif ($event->target_role === 'parent') {
                    $usersQuery->whereIn('id', $parentUserIds);
                } else {
                    $usersQuery->where(function ($q) use ($studentUserIds, $parentUserIds) {
                        $q->whereIn('id', $studentUserIds)
                            ->orWhereIn('id', $parentUserIds);
                    });
                }
            }

            $recipients = $usersQuery->whereNotNull('phone_number')->get();

            foreach ($recipients as $recipient) {
                // 1. Simpan in-app notification
                $notification = new EventReminderNotification($event);
                $recipient->notify($notification);

                // 2. Kirim WhatsApp
                try {
                    $notification->sendWhatsapp($recipient->phone_number);
                    $this->line("-> Berhasil mengirim notifikasi ke {$recipient->name} ({$recipient->phone_number})");
                } catch (\Exception $e) {
                    $this->error("-> Gagal mengirim notifikasi ke {$recipient->name}: ".$e->getMessage());
                }
            }
        }

        $this->info('Selesai memproses pengingat agenda.');

        return 0;
    }
}
