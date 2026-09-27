<?php

namespace App\Jobs;

use App\Models\Student;
use App\Models\User;
use App\Models\WhatsappBroadcast;
use App\Services\WhatsappAutomationService;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $broadcast;

    public function __construct(WhatsappBroadcast $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    public function handle(WhatsappService $whatsappService, WhatsappAutomationService $automationService)
    {
        $broadcast = $this->broadcast;
        if ($broadcast->status !== 'sending' && $broadcast->status !== 'scheduled') {
            return;
        }

        $broadcast->update(['status' => 'sending']);

        // 1. Gather recipients
        $recipients = collect();

        switch ($broadcast->target_type) {
            case 'all_parents':
                $recipients = User::where('role', 'parent')->whereNotNull('phone_number')->get();
                break;
            case 'all_teachers':
                $recipients = User::whereIn('role', ['guru', 'waka_kesiswaan', 'waka_kurikulum', 'waka_sarpras', 'waka_humas', 'kepala_sekolah'])
                    ->whereNotNull('phone_number')
                    ->get();
                break;
            case 'specific_class':
                $classroomIds = (array) $broadcast->target_ids;
                $students = Student::whereIn('classroom_id', $classroomIds)->with('parent')->get();
                $recipients = $students->map(fn ($s) => $s->parent)->filter()->unique('id');
                break;
            case 'custom':
                $userIds = (array) $broadcast->target_ids;
                $recipients = User::whereIn('id', $userIds)->whereNotNull('phone_number')->get();
                break;
        }

        $total = $recipients->count();
        $broadcast->update([
            'total_recipients' => $total,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        if ($total === 0) {
            $broadcast->update([
                'status' => 'completed',
                'sent_at' => now(),
            ]);

            return;
        }

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            // Determine placeholders context
            $context = [];
            if ($recipient->role === 'parent') {
                // Find first child
                $student = Student::where('parent_id', $recipient->id)->with('classroom')->first();
                $context = [
                    'nama_orang_tua' => $recipient->name,
                    'nama_siswa' => $student->name ?? '',
                    'kelas' => $student->classroom?->name ?? '',
                ];
            } else {
                $context = [
                    'nama_orang_tua' => $recipient->name,
                    'nama_siswa' => '',
                    'kelas' => '',
                ];
            }

            // Compile template
            $messageBody = $broadcast->message;
            if ($broadcast->template) {
                $messageBody = $automationService->parseMessage($broadcast->template->content, $context);
            }

            // Send via WhatsappService
            $log = $whatsappService->sendMessage($recipient->phone_number, $messageBody, 'broadcast_'.$broadcast->id);

            if ($log->status === 'sent') {
                $sent++;
            } else {
                $failed++;
            }

            // Update progressive counts in DB
            $broadcast->update([
                'sent_count' => $sent,
                'failed_count' => $failed,
            ]);

            // Rate-limit safety: 500ms delay between API hits
            usleep(500000);
        }

        $broadcast->update([
            'status' => 'completed',
            'sent_at' => now(),
        ]);
    }
}
