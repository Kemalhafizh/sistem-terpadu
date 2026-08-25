<?php

declare(strict_types=1);

namespace App\Services\Accessibility;

use App\Models\VoiceNavigationCommand;

class VoiceCommandProcessorService
{
    /**
     * Get active voice navigation commands dictionary
     */
    public function getActiveCommands(string $locale = 'id'): array
    {
        $commands = VoiceNavigationCommand::where('is_active', true)
            ->where('language_code', $locale)
            ->get();

        if ($commands->isEmpty()) {
            $defaultCommands = [
                ['language_code' => 'id', 'command_phrase' => 'buka rapor', 'action_url' => '/grades', 'target_module' => 'akademik'],
                ['language_code' => 'id', 'command_phrase' => 'lihat jadwal', 'action_url' => '/schedule', 'target_module' => 'jadwal'],
                ['language_code' => 'id', 'command_phrase' => 'bayar spp', 'action_url' => '/spp', 'target_module' => 'keuangan'],
                ['language_code' => 'id', 'command_phrase' => 'presensi harian', 'action_url' => '/attendance', 'target_module' => 'kesiswaan'],
            ];

            foreach ($defaultCommands as $cmd) {
                VoiceNavigationCommand::create($cmd);
            }

            $commands = VoiceNavigationCommand::where('is_active', true)->where('language_code', $locale)->get();
        }

        return $commands->toArray();
    }
}
