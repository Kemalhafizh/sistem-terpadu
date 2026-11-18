<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that manifest.json exists in the public directory and contains valid PWA info.
     */
    public function test_manifest_file_exists_and_is_valid()
    {
        $path = public_path('manifest.json');

        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        $this->assertNotNull($data, 'manifest.json is not valid JSON');
        $this->assertEquals('SISTA — SMA Islam Sultan Agung 1 Semarang', $data['name']);
        $this->assertEquals('SISTA', $data['short_name']);
        $this->assertEquals('standalone', $data['display']);
        $this->assertEquals('#0d9488', $data['theme_color']);
    }

    /**
     * Test that /sw.js serves valid Service Worker logic without caching API routes.
     */
    public function test_service_worker_file_exists_and_is_valid()
    {
        $response = $this->get('/sw.js');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('const CACHE_NAME =', $content);
        $this->assertStringContainsString('self.addEventListener(\'install\'', $content);
        $this->assertStringContainsString('self.addEventListener(\'fetch\'', $content);
        $this->assertStringContainsString('/api/', $content);
        $this->assertStringContainsString('/offline.html', $content);
    }

    /**
     * Test that offline.html exists in the public directory and contains proper markup.
     */
    public function test_offline_fallback_page_exists_and_is_valid()
    {
        $path = public_path('offline.html');

        $this->assertFileExists($path);

        $content = file_get_contents($path);
        $this->assertStringContainsString('Mode Luring Aktif', $content);
        $this->assertStringContainsString('Koneksi Anda Terputus', $content);
    }

    /**
     * Test that the main app layout includes PWA meta tags and registers the service worker.
     */
    public function test_app_layout_registers_pwa_metadata()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('<link rel="manifest" href="/manifest.json">', false);
        $response->assertSee('<meta name="theme-color" content="#0d9488">', false);
        $response->assertSee('navigator.serviceWorker.register(\'/sw.js\')', false);
        $response->assertSee('showPwaBanner', false);
    }
}
