<?php

use Illuminate\Routing\Route as RouteObject;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\SplFileInfo;

/*
 * Static guards for the classes of bug a crawl of every page turned up:
 * routes to controller methods that don't exist (unrestricted
 * Route::resource), route() calls to names that don't exist, and
 * controllers rendering views that were never written. Each one was an
 * HTTP 500 for whoever opened the page.
 */

/** @return array<string, array{0: string, 1: string}> action => [class, method] */
function routedControllerActions(): array
{
    $actions = [];
    foreach (Route::getRoutes() as $route) {
        /** @var RouteObject $route */
        $action = $route->getActionName();
        if ($action === 'Closure' || ! str_contains($action, '@')) {
            continue;
        }
        $actions[$route->methods()[0].' '.$route->uri()] = explode('@', $action, 2);
    }

    return $actions;
}

test('setiap route mengarah ke method controller yang ada', function () {
    $missing = collect(routedControllerActions())
        ->reject(fn (array $action) => class_exists($action[0]) && method_exists($action[0], $action[1]))
        ->map(fn (array $action, string $route) => "{$route} -> {$action[0]}@{$action[1]}")
        ->values();

    expect($missing->all())->toBe([]);
});

test('setiap route() di view dan kode memakai nama route yang terdaftar', function () {
    $pattern = '/(?<![\w>:])(?:route|to_route)\(\s*[\'"]([\w.\-]+)[\'"]|->route\(\s*[\'"]([\w.\-]+)[\'"]/';

    $unknown = collect(File::allFiles(resource_path('views')))
        ->merge(File::allFiles(app_path()))
        ->flatMap(function (SplFileInfo $file) use ($pattern) {
            $source = $file->getContents();
            preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);

            return collect($matches)
                ->map(fn (array $m) => $m[2] ?? '' ?: $m[1])
                // $this->route('student') in form requests reads a route parameter.
                ->reject(fn (string $name) => in_array($name, ['student', 'user', 'token'], true))
                ->reject(fn (string $name) => Route::has($name))
                // Links guarded with Route::has('name') are optional on purpose.
                ->reject(fn (string $name) => str_contains($source, "Route::has('{$name}')"))
                ->map(fn (string $name) => $file->getRelativePathname().": {$name}");
        })
        ->unique()
        ->values();

    expect($unknown->all())->toBe([]);
});

test('setiap view yang dirender action yang di-route memang ada', function () {
    $routed = collect(routedControllerActions())->map(fn (array $a) => $a[0].'@'.$a[1])->flip();

    $missing = collect(File::allFiles(app_path('Http/Controllers')))->flatMap(function (SplFileInfo $file) use ($routed) {
        $source = $file->getContents();
        preg_match('/^namespace ([\w\\\\]+);/m', $source, $ns);
        $class = ($ns[1] ?? '').'\\'.$file->getFilenameWithoutExtension();

        preg_match_all('/(?:\bview|response\(\)->view)\(\s*[\'"]([\w.\-\/]+)[\'"]/', $source, $views, PREG_OFFSET_CAPTURE);

        return collect($views[1])->map(function (array $view) use ($source, $class, $routed) {
            preg_match_all('/public function (\w+)\(/', substr($source, 0, $view[1]), $methods);
            $method = end($methods[1]) ?: '';

            return $routed->has("{$class}@{$method}") && ! view()->exists($view[0]) ? "{$class}@{$method}: {$view[0]}" : null;
        })->filter();
    })->values();

    expect($missing->all())->toBe([]);
});
