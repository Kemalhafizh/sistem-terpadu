<?php

use Illuminate\Support\Facades\File;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

test('developer portal dashboard (README) renders successfully', function () {
    $response = $this->get('/developer');

    $response->assertStatus(200);
    $response->assertSee('SISTA Developer Portal');
    $response->assertSee('Overview &amp; Portal Hub', false);
    $response->assertSee('Peta Navigasi Dokumentasi');
});

test('developer portal can render top-level documentation guides', function () {
    $response = $this->get('/developer/ARCHITECTURE');

    $response->assertStatus(200);
    $response->assertSee('Arsitektur & Pola Desain Sistem SISTA', false);
    $response->assertSee('Service Pattern');
    $response->assertSee('Workflow Engine');
});

test('developer portal can render adr sub-folder guides', function () {
    $response = $this->get('/developer/adr/001-service-pattern');

    $response->assertStatus(200);
    $response->assertSee('ADR 001: Penerapan Service Pattern');
    $response->assertSee('Konteks & Masalah', false);
    $response->assertSee('Keputusan yang Diambil');
});

test('developer portal can render business module subpage guides', function () {
    $response = $this->get('/developer/modules/academic');

    $response->assertStatus(200);
    $response->assertSee('Modul Akademik SISTA');
    $response->assertSee('Rapor Digital & Penilaian', false);
});

test('developer portal can render new premium module subpages successfully', function () {
    $modules = [
        'elearning' => 'Modul E-Learning',
        'communication' => 'Modul WhatsApp Gateway',
        'import_export' => 'Modul Universal Data Import/Export Hub SISTA',
        'workflow' => 'Modul Polymorphic Approval Workflow Engine SISTA',
        'portfolio' => 'Modul Student e-Portfolio SISTA',
        'security' => 'Modul Monitoring Keamanan SIEM',
        'report_builder' => 'Modul Drag-Drop Custom Report Builder SISTA',
        'chatbot' => 'Modul AI Assistant Chatbot Sekolah (Lokal) SISTA',
        'gamification' => 'Modul Gamifikasi & Engagement System Siswa SISTA',
    ];

    foreach ($modules as $slug => $title) {
        $response = $this->get("/developer/modules/{$slug}");
        $response->assertStatus(200);
        $response->assertSee($title, false);
    }
});

test('developer portal returns 404 for non-existent documentation files', function () {
    $response = $this->get('/developer/non-existent-documentation-file-slug');

    $response->assertStatus(404);
});

test('interactive scribe api documentation endpoint is accessible', function () {
    $response = $this->get('/docs');

    $response->assertStatus(200);
    $response->assertSee('Sistem Terpadu Sultan Agung 1 API');
});
