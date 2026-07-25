<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Communication\MessagingService;
use Illuminate\Database\Seeder;

class InternalMessagingSeeder extends Seeder
{
    public function run(): void
    {
        $messagingService = app(MessagingService::class);
        $users = User::take(3)->get();

        if ($users->count() < 2) {
            return;
        }

        $user1 = $users[0];
        $user2 = $users[1];

        // Seed Direct Conversation
        $directConv = $messagingService->createDirectConversation(
            $user1->id,
            $user2->id,
            'Assalamu`alaikum Ustadz, mohon informasi jadwal rapat koordinasi akademik besok.'
        );

        $messagingService->sendMessage(
            $directConv->id,
            $user2->id,
            'Wa`alaikumussalam Wr. Wb. Rapat koordinasi insyaAllah dilaksanakan jam 09:00 WIB di ruang guru.'
        );

        // Seed Group Conversation if 3 users exist
        if ($users->count() >= 3) {
            $user3 = $users[2];
            $groupConv = $messagingService->createGroupConversation(
                $user1->id,
                'Koordinasi Panitia Penilaian Akhir Semester (PAS)',
                [$user2->id, $user3->id],
                'Bismillah, selamat datang panitia PAS SMA Islam Sultan Agung 1 Semarang.'
            );

            $messagingService->sendMessage(
                $groupConv->id,
                $user3->id,
                'Siap Ustadz, bank soal CBT sebagian besar sudah selesai disusun oleh guru-guru saintek.'
            );
        }
    }
}
