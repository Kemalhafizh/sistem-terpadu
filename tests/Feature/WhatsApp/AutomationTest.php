<?php

use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappTemplate;

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

test('admin can access whatsapp automations list', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.whatsapp.automations.index'));
    $response->assertStatus(200);
});

test('admin can create automation rule', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.automations.store'), [
        'name' => 'Kirim Notifikasi SPP Jatuh Tempo',
        'trigger' => 'billing_due_3_days',
        'template_id' => $this->template->id,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('whatsapp_automations', [
        'trigger' => 'billing_due_3_days',
        'name' => 'Kirim Notifikasi SPP Jatuh Tempo',
        'template_id' => $this->template->id,
    ]);
});

test('admin can toggle automation rule status', function () {
    $automation = WhatsappAutomation::create([
        'name' => 'Kirim Absensi Alpa',
        'trigger' => 'attendance_absent',
        'template_id' => $this->template->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.whatsapp.automations.toggle', $automation->id));

    $response->assertRedirect();
    $automation->refresh();
    expect($automation->is_active)->toBe(false);
});
