<?php

namespace App\Support;

/**
 * Reads the end of a (possibly large) log file without loading all of it.
 */
final class LogTail
{
    /**
     * The last $maxLines complete lines within the last $maxBytes of the file.
     * Returns [] when the file doesn't exist.
     *
     * @return string[]
     */
    public static function lines(string $path, int $maxBytes, int $maxLines): array
    {
        if (! is_file($path)) {
            return [];
        }

        clearstatcache(true, $path);
        $size = filesize($path);
        if ($size === false) {
            throw new \RuntimeException('Log file size could not be read.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Log file could not be opened.');
        }

        try {
            $offset = max(0, $size - max(1, $maxBytes));
            fseek($handle, $offset);
            $chunk = (string) stream_get_contents($handle);
        } finally {
            fclose($handle);
        }

        $lines = explode("\n", $chunk);
        if ($offset > 0) {
            // Reading started mid-file, so the first line is almost always partial.
            array_shift($lines);
        }
        if (end($lines) === '') {
            array_pop($lines); // trailing newline
        }

        return array_slice($lines, -max(1, $maxLines));
    }
}
