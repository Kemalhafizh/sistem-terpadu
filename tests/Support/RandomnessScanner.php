<?php

namespace Tests\Support;

/**
 * Finds every place app code makes up a value at random: rand(), mt_rand(),
 * random_int(), array_rand(), shuffle(), fake(), Faker, and ->random(),
 * ->shuffle(), ->inRandomOrder(). Str::random() (tokens, file names) is not
 * counted. Reads PHP tokens, so comments and strings never match.
 */
final class RandomnessScanner
{
    private const FUNCTIONS = ['rand', 'mt_rand', 'random_int', 'array_rand', 'shuffle', 'str_shuffle', 'lcg_value', 'mt_srand', 'srand', 'fake'];

    private const METHODS = ['random', 'shuffle', 'inRandomOrder', 'randomElement', 'randomNumber', 'randomFloat'];

    /**
     * @return array<string, array<string, list<int>>> relative path => call name => line numbers
     */
    public static function scan(string $root, string $dir): array
    {
        $found = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relative = ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($root))), '/');
            foreach (self::callsIn((string) file_get_contents($file->getPathname())) as [$name, $line]) {
                $found[$relative][$name][] = $line;
            }
        }
        ksort($found);

        return $found;
    }

    /**
     * @return list<array{0: string, 1: int}>
     */
    public static function callsIn(string $code): array
    {
        $tokens = array_values(array_filter(
            token_get_all($code),
            fn ($t) => ! is_array($t) || ! in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true),
        ));
        $calls = [];
        foreach ($tokens as $i => $token) {
            if (! is_array($token)) {
                continue;
            }
            $next = $tokens[$i + 1] ?? null;
            $prev = $tokens[$i - 1] ?? null;
            $prevType = is_array($prev) ? $prev[0] : $prev;

            if (in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true) && str_starts_with(ltrim($token[1], '\\'), 'Faker\\')) {
                $calls[] = ['Faker', $token[2]];

                continue;
            }
            if ($next !== '(') {
                continue;
            }
            $name = in_array($token[0], [T_STRING, T_NAME_FULLY_QUALIFIED], true) ? ltrim($token[1], '\\') : null;
            if ($name === null) {
                continue;
            }
            $isMember = in_array($prevType, [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON], true);
            if ($isMember && in_array($name, self::METHODS, true)) {
                $owner = $tokens[$i - 2] ?? null;
                $isStr = $prevType === T_DOUBLE_COLON && is_array($owner) && in_array(ltrim($owner[1], '\\'), ['Str', 'Illuminate\\Support\\Str'], true);
                if (! $isStr) {
                    $calls[] = ['->'.$name, $token[2]];
                }
            } elseif (! $isMember && $prevType !== T_FUNCTION && $prevType !== T_NEW && in_array(strtolower($name), self::FUNCTIONS, true)) {
                $calls[] = [strtolower($name), $token[2]];
            }
        }

        return $calls;
    }
}
