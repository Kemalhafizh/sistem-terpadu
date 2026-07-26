<?php

namespace Tests\Feature\Deployment;

use App\Models\User;
use App\Services\Deployment\DatabaseSeedingService;
use App\Services\Deployment\E2eTestService;
use App\Services\Deployment\EnvironmentConfigService;
use App\Services\Deployment\ReleaseManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin']);
    }

    /** @test */
    public function test_38_1_production_environment_and_secret_management()
    {
        $service = app(EnvironmentConfigService::class);

        $config = $service->setConfig('DB_PASS', 'secret123', 'production', 'database', true, $this->admin->name);
        $this->assertEquals('production', $config->environment);

        $val = $service->getConfig('DB_PASS', 'production');
        $this->assertEquals('secret123', $val);

        $health = $service->validateEnvironment();
        $this->assertEquals('healthy', $health['status']);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('deploy.environment.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Production Environment');
    }

    /** @test */
    public function test_38_2_database_seeding_strategy_and_anonymization()
    {
        $service = app(DatabaseSeedingService::class);

        $staging = $service->seedStaging();
        $this->assertTrue($staging['anonymized']);

        $prod = $service->seedProduction();
        $this->assertTrue($prod['essential_lookups_only']);
    }

    /** @test */
    public function test_38_3_release_versioning_and_rollback_strategy()
    {
        $service = app(ReleaseManagerService::class);

        $release = $service->createRelease('1.0.0', 'Changelog Rilis Perdana', 'major', $this->admin->name);
        $this->assertEquals('1.0.0', $release->version);
        $this->assertTrue($release->rollback_tested);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('deploy.releases.index'));
        $response->assertStatus(200);
        $response->assertSee('Release Versioning');
    }

    /** @test */
    public function test_38_4_comprehensive_e2e_testing_suite()
    {
        $service = app(E2eTestService::class);

        $run = $service->runSuite();
        $this->assertEquals('PASSED', $run->status);
        $this->assertEquals(10, $run->total_tests);

        // HTTP View Test
        $response = $this->actingAs($this->admin)->get(route('deploy.e2e.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Comprehensive E2E Testing Suite Dashboard');
    }
}
