<?php

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

/*
 * @json() and {!! json_encode() !!} print raw double quotes. Inside a
 * double-quoted attribute (x-data="{ rows: @json($rows) }") the first quote
 * ends the attribute, Alpine gets half an expression and the whole component
 * dies — e.g. the UKS visit form threw "SyntaxError: Unexpected token ';'".
 * Use {{ Js::from($value) }} there: it is escaped, so it is attribute-safe.
 */
test('JSON di dalam atribut Alpine selalu di-escape', function () {
    $attribute = '/\s((?:x-[\w.:-]+|:[\w.-]+|@[\w.:-]+)=)"([^"]*)"/s';

    $offenders = collect(File::allFiles(resource_path('views')))
        ->flatMap(function (SplFileInfo $file) use ($attribute) {
            $source = $file->getContents();
            preg_match_all($attribute, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

            return collect($matches)
                ->filter(fn (array $m) => str_contains($m[2][0], '@json(') || preg_match('/\{!!\s*json_encode/', $m[2][0]))
                ->map(fn (array $m) => $file->getRelativePathname().':'.(substr_count(substr($source, 0, $m[0][1]), "\n") + 1).' '.$m[1][0]);
        })
        ->values();

    expect($offenders->all())->toBe([]);
});
