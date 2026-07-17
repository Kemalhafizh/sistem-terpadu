<?php

use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Models\WhatsappBroadcast;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendWhatsappBroadcastJob;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->template = WhatsappTemplate::create([
        'name' => 'SPP Reminder Template',
        'category' => 'keuangan',
        'content' => 'Halo {nama_siswa}, ini reminder SPP.',
        'placeholders' => ['nama_siswa'],
        'is_active' => true,
    ]);
});

test('admin can access whatsapp broadcasts index', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.whatsapp.broadcasts.index'));
    $response->assertStatus(200);
});

test('admin can create manual whatsapp broadcast', function () {
    Bus::fake();

    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.broadcasts.store'), [
        'title' => 'Broadcast Pengumuman Libur',
        'message' => 'Sekolah akan libur besok.',
        'target_type' => 'all_teachers',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('whatsapp_broadcasts', [
        'title' => 'Broadcast Pengumuman Libur',
        'message' => 'Sekolah akan libur besok.',
        'target_type' => 'all_teachers',
        'status' => 'sending',
    ]);

    Bus::assertDispatched(SendWhatsappBroadcastJob::class);
});

test('admin can create broadcast using template', function () {
    Bus::fake();

    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.broadcasts.store'), [
        'title' => 'Broadcast SPP Bulanan',
        'template_id' => $this->template->id,
        'target_type' => 'all_parents',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('whatsapp_broadcasts', [
        'title' => 'Broadcast SPP Bulanan',
        'template_id' => $this->template->id,
        'message' => 'Halo {nama_siswa}, ini reminder SPP.',
        'target_type' => 'all_parents',
    ]);

    Bus::assertDispatched(SendWhatsappBroadcastJob::class);
});
