<?php

declare(strict_types=1);

namespace Tests\Feature\SuperApp;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase83ModuleDiscoveryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_module_discovery_config(): void
    {
        $response = $this->getJson('/api/v1/mobile/config');
        $response->assertStatus(200);
        $response->assertJsonStructure(['features', 'app_name']);
    }
}
