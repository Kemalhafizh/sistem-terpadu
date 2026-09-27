<?php

namespace Tests\Feature\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class NoEmojiGovernanceTest extends TestCase
{
    /**
     * Pastikan tidak ada karakter emoji di views, controllers, services, dan seeders.
     */
    public function test_views_controllers_services_and_seeders_must_not_contain_emojis(): void
    {
        // Pattern untuk Unicode Emojis (Emoji & Pictographs)
        $emojiPattern = '/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F600}-\x{1F64F}\x{1F680}-\x{1F6FF}]/u';

        $directories = [
            resource_path('views'),
            app_path('Http/Controllers'),
            app_path('Services'),
            database_path('seeders'),
        ];

        $filesWithEmoji = [];

        foreach ($directories as $dir) {
            if (! is_dir($dir)) {
                continue;
            }
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php'])) {
                    $content = file_get_contents($file->getPathname());
                    if (preg_match($emojiPattern, $content)) {
                        $filesWithEmoji[] = str_replace(base_path(), '', $file->getPathname());
                    }
                }
            }
        }

        $this->assertEmpty(
            $filesWithEmoji,
            'Terdapat emoji pada '.count($filesWithEmoji)." berkas berikut. Harap ganti dengan ikon SVG / teks formal:\n".implode("\n", $filesWithEmoji)
        );
    }
}
