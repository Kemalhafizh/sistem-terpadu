<?php

use App\Models\ApiClient;
use App\Models\Plugin;
use App\Models\User;
use App\Services\Integration\ApiDocumentationService;
use App\Services\Integration\EtlPipelineService;
use App\Services\Integration\GraphqlService;
use App\Services\Integration\PluginManagerService;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin', 'name' => 'Integration Engineer']);
    $this->graphqlService = app(GraphqlService::class);
    $this->etlService = app(EtlPipelineService::class);
    $this->pluginService = app(PluginManagerService::class);
    $this->docService = app(ApiDocumentationService::class);
});

// 31.1 GraphQL API Federation & Gateway
test('graphql service resolves query and mutation payloads', function () {
    $queryRes = $this->graphqlService->resolveQuery('{ students { id name } }');
    expect($queryRes['data']['students'])->toBeArray();

    $mutationRes = $this->graphqlService->resolveMutation('mutation { updateStudent }');
    expect($mutationRes['data']['updateStudent']['success'])->toBeTrue();

    $intro = $this->graphqlService->introspection();
    expect($intro['__schema'])->not()->toBeNull();

    $endpointResponse = $this->actingAs($this->user)->postJson(route('api.graphql'), [
        'query' => '{ institution { name npsn } }',
    ]);
    $endpointResponse->assertStatus(200);
    // The school's NPSN from Profil Sekolah / config — was a hard-coded placeholder (20328888).
    $endpointResponse->assertJsonPath('data.institution.npsn', config('school.npsn'));
});

test('admin can register api clients and view usage stats', function () {
    $registerResponse = $this->actingAs($this->user)->post(route('api.register_client'), [
        'name' => 'Third-Party Mobile App',
        'rate_limit_per_minute' => 100,
    ]);
    $registerResponse->assertRedirect();
    expect(ApiClient::where('name', 'Third-Party Mobile App')->exists())->toBeTrue();

    $statsResponse = $this->actingAs($this->user)->get(route('api.stats'));
    $statsResponse->assertStatus(200);
});

// 31.2 Enterprise ETL Pipeline & Data Migration Toolkit
test('etl pipeline service executes extract transform load flow', function () {
    $job = $this->etlService->createJob([
        'name' => 'Import Nilai Legacy Excel',
        'source_type' => 'excel',
        'source_file_path' => 'imports/nilai_legacy.xlsx',
        'target_table' => 'grades',
        'records_total' => 200,
    ], $this->user->id);

    expect($job->status)->toBe('pending');

    $executedJob = $this->etlService->execute($job->id);
    expect($executedJob->status)->toBe('completed');
    expect($executedJob->records_processed)->toBe(200);

    $rolledBack = $this->etlService->rollback($job->id);
    expect($rolledBack->status)->toBe('failed');
});

test('admin can configure etl mapping and view progress via controller', function () {
    $job = $this->etlService->createJob(['name' => 'Controller ETL Job', 'source_file_path' => 'file.csv'], $this->user->id);

    $mappingResponse = $this->actingAs($this->user)->get(route('etl.configure_mapping', $job->id));
    $mappingResponse->assertStatus(200);

    $execResponse = $this->actingAs($this->user)->post(route('etl.execute', $job->id));
    $execResponse->assertRedirect();

    $progressResponse = $this->actingAs($this->user)->get(route('etl.progress', $job->id));
    $progressResponse->assertStatus(200);
});

// 31.3 Third-Party Plugin Architecture & Extension Registry
test('plugin manager service installs enables and executes event hooks', function () {
    $plugin = $this->pluginService->install([
        'name' => 'Widget Chatbot Support',
        'slug' => 'widget-chatbot',
        'version' => '1.0.0',
        'author' => 'AI Partner',
    ]);

    expect($plugin->is_enabled)->toBeTrue();

    $hook = $this->pluginService->registerHook($plugin->id, 'dashboard.widget', 'App\\Plugins\\Chatbot', 'render');
    expect($hook->id)->not()->toBeNull();

    $executedHooks = $this->pluginService->executeHook('dashboard.widget');
    expect($executedHooks)->toHaveCount(1);
    expect($executedHooks[0]['plugin'])->toBe('Widget Chatbot Support');

    $toggled = $this->pluginService->toggle($plugin->id, false);
    expect($toggled->is_enabled)->toBeFalse();
});

test('admin can access plugin manager and marketplace controller endpoints', function () {
    $indexResponse = $this->actingAs($this->user)->get(route('plugins.index'));
    $indexResponse->assertStatus(200);

    $marketplaceResponse = $this->actingAs($this->user)->get(route('plugins.marketplace'));
    $marketplaceResponse->assertStatus(200);
});

// 31.4 Public API Documentation Portal (OpenAPI 3.0)
test('api documentation service generates openapi spec and postman exports', function () {
    $spec = $this->docService->generateOpenApiSpec();
    expect($spec['openapi'])->toBe('3.0.3');
    expect($spec['info']['title'])->toBe('SISTA Enterprise Core API');

    $postman = $this->docService->generatePostmanCollection();
    expect($postman['info']['name'])->toBe('SISTA Enterprise API Collection');

    $portalResponse = $this->actingAs($this->user)->get(route('api_docs.index'));
    $portalResponse->assertStatus(200);

    $jsonResponse = $this->actingAs($this->user)->get(route('api_docs.openapi_json'));
    $jsonResponse->assertStatus(200);
    $jsonResponse->assertJsonPath('openapi', '3.0.3');

    $swaggerResponse = $this->actingAs($this->user)->get(route('api_docs.swagger_ui'));
    $swaggerResponse->assertStatus(200);
});
