<?php

/*
 * Guard architecture/UI test ensuring Blade views, public scripts, routes, and
 * project Markdown docs remain free of decorative blur/ping UI slop, emojis,
 * UTF-8 mojibake, and leftover phase scaffolding tags.
 */

test('blade views bebas dari efek dekoratif berlebihan dan komentar slop', function () {
    $root = base_path();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(resource_path('views'), RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $pattern = '/\b(?:blur-2xl|blur-3xl|bg-clip-text|animate-ping|Premium|Glassmorphism)\b|═{3,}|\bFASE\s+\d+/iu';
    $violations = [];

    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $relative = str_replace($root.DIRECTORY_SEPARATOR, '', $file->getPathname());
        $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            continue;
        }

        foreach ($lines as $index => $line) {
            if (preg_match($pattern, $line) === 1) {
                $violations[] = $relative.':'.($index + 1);
            }
        }
    }

    expect($violations)->toBe([]);
});

test('routes api dan aset statis bebas mojibake, emoji dekoratif, dan banner pembatas', function () {
    $root = base_path();
    $files = [
        'routes/api.php',
        'public/offline-queue.js',
        'public/css/print.css',
    ];

    $emojiRegex = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u';
    $dividerRegex = '/^\s*\/\/\s*[=\-─═]{5,}\s*$/u';
    $mojibakeRegex = '/(?:â€”|ðŸ)/';
    $violations = [];

    foreach ($files as $relative) {
        $path = base_path($relative);
        if (! is_file($path)) {
            continue;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            continue;
        }

        foreach ($lines as $index => $line) {
            $lineNo = $index + 1;
            if (preg_match($emojiRegex, $line) === 1) {
                $violations[] = $relative.':'.$lineNo.' (emoji)';
            }
            if (preg_match($dividerRegex, $line) === 1) {
                $violations[] = $relative.':'.$lineNo.' (divider)';
            }
            if (preg_match($mojibakeRegex, $line) === 1) {
                $violations[] = $relative.':'.$lineNo.' (mojibake)';
            }
        }
    }

    expect($violations)->toBe([]);
});

test('dokumentasi markdown bebas emoji dekoratif dan istilah marketing AI', function () {
    $root = base_path();
    $docRoots = [
        base_path('docs'),
        base_path('tests/Load'),
    ];
    $singleDocs = [
        base_path('README.md'),
        base_path('CONTRIBUTING.md'),
    ];

    $emojiRegex = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u';
    $buzzwordRegex = '/\b(?:glassmorphic|glassmorphism|cutting-edge|state-of-the-art)\b/i';
    $files = $singleDocs;

    foreach ($docRoots as $dir) {
        if (! is_dir($dir)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.md')) {
                $files[] = $file->getPathname();
            }
        }
    }

    $violations = [];
    foreach ($files as $path) {
        if (! is_file($path)) {
            continue;
        }
        $relative = str_replace($root.DIRECTORY_SEPARATOR, '', $path);
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            continue;
        }

        foreach ($lines as $index => $line) {
            $lineNo = $index + 1;
            if (preg_match($emojiRegex, $line) === 1) {
                $violations[] = $relative.':'.$lineNo.' (emoji)';
            }
            if (preg_match($buzzwordRegex, $line) === 1) {
                $violations[] = $relative.':'.$lineNo.' (buzzword)';
            }
        }
    }

    expect($violations)->toBe([]);
});
