<?php

/*
 * A public method on an API controller is an endpoint. One without a route is
 * dead code that still looks like a feature: MobileV2ApiController carried
 * thirteen of them, including a parent "activity feed" of made-up events and
 * notification preferences that were never saved.
 */

test('setiap method publik controller API v1 punya rute', function () {
    $routed = collect(app('router')->getRoutes())->map->getActionName()->flip();
    $unrouted = [];

    $base = app_path('Http/Controllers/Api/v1/');
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $class = 'App\\Http\\Controllers\\Api\\v1\\'.str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($base)));
        $reflection = new ReflectionClass($class);
        if ($reflection->isAbstract()) {
            continue;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $class || $method->isConstructor() || $method->isStatic()) {
                continue;
            }
            // Invokable controllers are routed by class name alone.
            $action = $method->name === '__invoke' ? $class : "{$class}@{$method->name}";
            if (! $routed->has($action)) {
                $unrouted[] = class_basename($class).'::'.$method->name;
            }
        }
    }

    expect($unrouted)->toBe([]);
});
