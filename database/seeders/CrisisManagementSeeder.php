<?php

namespace Database\Seeders;

use App\Models\BullyingReport;
use App\Models\CrisisIncident;
use App\Models\CrisisProtocol;
use App\Models\CrisisResponseLog;
use App\Models\EmergencyBroadcast;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CrisisManagementSeeder extends Seeder
{
    public function run()
    {
        CrisisProtocol::create([
            'name' => 'Protokol Gempa Bumi Standar',
            'crisis_type' => 'gempa_bumi',
            'steps' => ['Berlindung di bawah meja', 'Evakuasi ke lapangan', 'Hitung jumlah siswa'],
            'emergency_contacts' => ['BPBD' => '112', 'Ambulans' => '118'],
            'assembly_points' => ['Lapangan Utama', 'Lapangan Basket'],
        ]);

        CrisisProtocol::create([
            'name' => 'Protokol Kebakaran',
            'crisis_type' => 'kebakaran',
            'steps' => ['Bunyikan alarm', 'Evakuasi melalui tangga darurat', 'Gunakan APAR jika api kecil'],
            'emergency_contacts' => ['Pemadam' => '113'],
            'assembly_points' => ['Lapangan Utama'],
        ]);

        CrisisProtocol::create([
            'name' => 'Protokol Pandemi',
            'crisis_type' => 'pandemi',
            'steps' => ['Isolasi area', 'Wajib masker', 'Disinfeksi berkala'],
            'emergency_contacts' => ['Dinkes' => '119'],
            'assembly_points' => ['Ruang Isolasi UKS'],
        ]);

        $resolvedGempa = CrisisIncident::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Gempa Ringan Skala 4.0 SR',
            'type' => 'gempa_bumi',
            'severity' => 'level_2_moderate',
            'status' => 'resolved',
            'description' => 'Guncangan terasa selama 5 detik.',
            'detected_at' => now()->subDays(10),
            'responded_at' => now()->subDays(10)->addMinutes(2),
            'resolved_at' => now()->subDays(10)->addHours(1),
            'reported_by' => 1,
            'post_mortem_notes' => 'Siswa evakuasi dengan tertib. Tidak ada korban.',
        ]);

        $activeKebakaran = CrisisIncident::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Kebakaran Ringan Laboratorium',
            'type' => 'kebakaran',
            'severity' => 'level_3_major',
            'status' => 'responding',
            'description' => 'Asap terlihat dari jendela laboratorium kimia.',
            'detected_at' => now()->subMinutes(15),
            'responded_at' => now()->subMinutes(10),
            'reported_by' => 1,
        ]);

        CrisisResponseLog::create([
            'crisis_incident_id' => $activeKebakaran->id,
            'responder_id' => 1,
            'action_type' => 'evakuasi_dimulai',
            'description' => 'Seluruh siswa lab dievakuasi ke lapangan utama.',
        ]);

        EmergencyBroadcast::create([
            'uuid' => (string) Str::uuid(),
            'title' => 'Peringatan Asap Tebal',
            'message' => 'Mohon hindari area gedung utara. Sedang dilakukan pemadaman.',
            'priority' => 'warning',
            'target_groups' => ['guru', 'siswa'],
            'channels' => ['whatsapp', 'sms'],
            'total_recipients' => 500,
            'delivered_count' => 480,
            'sent_by' => 1,
            'sent_at' => now()->subMinutes(10),
        ]);

        BullyingReport::create([
            'ticket_number' => (string) Str::uuid(),
            'description' => 'Ada siswa yang dipalak di kantin.',
            'type' => 'pemerasan',
            'severity' => 'sedang',
            'status' => 'investigating',
            'is_anonymous' => true,
        ]);

        BullyingReport::create([
            'ticket_number' => (string) Str::uuid(),
            'description' => 'Ejekan fisik di kelas 10A.',
            'type' => 'verbal',
            'severity' => 'ringan',
            'status' => 'closed',
            'is_anonymous' => true,
        ]);
    }
}
