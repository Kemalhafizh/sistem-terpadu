<?php

use Symfony\Component\Yaml\Yaml;

/*
 * The deploy workflow ran on every merge to main and failed with "missing
 * server host": the school has no production server yet, so the SSH secrets
 * are empty. It also kept the maintenance-mode bypass secret in the repo.
 */

function deploySteps(): array
{
    $workflow = Yaml::parseFile(base_path('.github/workflows/deploy.yml'));

    return collect($workflow['jobs']['deploy']['steps'])->keyBy('name')->all();
}

test('deploy lewat SSH hanya jalan bila server produksi sudah diatur', function () {
    $steps = deploySteps();

    expect($steps['Check Production Server Configuration']['env']['SERVER_HOST'])->toBe('${{ secrets.SERVER_HOST }}')
        ->and($steps['Deploy Code via Secure SSH']['if'])->toBe("steps.server.outputs.configured == 'true'");
});

test('rahasia bypass mode pemeliharaan tidak ditulis di repo', function () {
    $script = deploySteps()['Deploy Code via Secure SSH']['with']['script'];

    expect($script)->toContain('php artisan down')
        ->not->toContain('--secret=');
});
