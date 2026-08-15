<?php

$dirsToScan = [
    __DIR__ . '/../lang',
    __DIR__ . '/../resources/views/layouts',
    __DIR__ . '/../resources/views/components',
    __DIR__ . '/../app/Http/Controllers',
    __DIR__ . '/../app/Http/Middleware',
    __DIR__ . '/../tests/Feature/Localization'
];

$emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{2300}-\x{23FF}\x{2B50}\x{2934}\x{2935}\x{25AA}\x{25AB}\x{25FE}\x{25FD}\x{25FB}\x{25FC}\x{2B1B}\x{2B1C}\x{200D}]/u';

$foundCount = 0;
$affectedFiles = [];

foreach ($dirsToScan as $dir) {
    if (!is_dir($dir)) continue;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        $content = file_get_contents($file->getPathname());
        if (preg_match_all($emojiPattern, $content, $matches)) {
            $foundCount += count($matches[0]);
            $affectedFiles[] = [
                'file' => $file->getPathname(),
                'count' => count($matches[0]),
                'chars' => array_unique($matches[0])
            ];
        }
    }
}

echo "=== ZERO EMOJI AUDIT REPORT ===\n";
echo "Total occurrences found: $foundCount\n";
echo "Total files affected: " . count($affectedFiles) . "\n";
if ($foundCount > 0) {
    foreach ($affectedFiles as $af) {
        echo "- " . $af['file'] . " (" . $af['count'] . " emojis: " . implode(' ', $af['chars']) . ")\n";
    }
} else {
    echo "SUCCESS: 100% Zero Emoji Compliance Verified!\n";
}
