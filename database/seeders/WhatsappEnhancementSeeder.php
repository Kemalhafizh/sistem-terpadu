<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class WhatsappEnhancementSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Create 5 WhatsApp templates
        $templatesData = [
            [
                'name' => 'Notifikasi Tagihan SPP Bulanan',
                'cat' => 'keuangan',
                'content' => 'Assalamu\'alaikum Wr. Wb. Yth. Bapak/Ibu {nama_orang_tua}, diberitahukan bahwa tagihan bulanan SPP {nama_siswa} sebesar Rp {tagihan} untuk bulan {bulan} telah diterbitkan. Batas akhir pembayaran adalah {batas_waktu}. Terima kasih. Wassalamu\'alaikum Wr. Wb.',
                'placeholders' => ['nama_orang_tua', 'nama_siswa', 'tagihan', 'bulan', 'batas_waktu'],
            ],
            [
                'name' => 'Laporan Hasil Penilaian Harian',
                'cat' => 'akademik',
                'content' => 'Assalamu\'alaikum Wr. Wb. Diberitahukan kepada wali murid dari {nama_siswa}, berikut hasil nilai Penilaian Harian mata pelajaran {mata_pelajaran} yang dilaksanakan pada {tanggal}: Nilai: {nilai} (KKM: {kkm}). Status: {status_kelulusan}. Wassalamu\'alaikum Wr. Wb.',
                'placeholders' => ['nama_siswa', 'mata_pelajaran', 'tanggal', 'nilai', 'kkm', 'status_kelulusan'],
            ],
            [
                'name' => 'Notifikasi Pelanggaran Disiplin Siswa (BK)',
                'cat' => 'akademik',
                'content' => 'Assalamu\'alaikum Wr. Wb. Yth. Orang Tua dari {nama_siswa}, diinformasikan bahwa putra/putri Anda hari ini tercatat melakukan pelanggaran: "{jenis_pelanggaran}" dengan akumulasi poin pelanggaran saat ini: {poin_akumulasi} poin. Mohon pembinaan bersama di rumah. Wassalamu\'alaikum Wr. Wb.',
                'placeholders' => ['nama_siswa', 'jenis_pelanggaran', 'poin_akumulasi'],
            ],
            [
                'name' => 'Undangan Rapat Orang Tua Wali Murid',
                'cat' => 'kegiatan',
                'content' => 'Assalamu\'alaikum Wr. Wb. Yth. Orang Tua Wali Murid Kelas {kelas}, kami mengundang Bapak/Ibu untuk menghadiri Rapat Koordinasi Perkembangan Akademik yang akan dilaksanakan pada: Hari/Tgl: {hari_tanggal}, Waktu: {waktu}, Tempat: Aula SMA Islam Sultan Agung 1. Kehadiran Bapak/Ibu sangat kami harapkan. Wassalamu\'alaikum Wr. Wb.',
                'placeholders' => ['kelas', 'hari_tanggal', 'waktu'],
            ],
            [
                'name' => 'Informasi Kegiatan & Berita Sekolah',
                'cat' => 'umum',
                'content' => 'Assalamu\'alaikum Wr. Wb. Dapatkan informasi terbaru mengenai agenda sekolah: "{nama_kegiatan}" yang akan diselenggarakan mulai tanggal {tanggal_mulai} s.d {tanggal_selesai}. Mari sukseskan bersama kegiatan SMA Islam Sultan Agung 1 Semarang. Wassalamu\'alaikum Wr. Wb.',
                'placeholders' => ['nama_kegiatan', 'tanggal_mulai', 'tanggal_selesai'],
            ],
        ];

        $templates = collect();
        foreach ($templatesData as $temp) {
            $t = WhatsappTemplate::create([
                'name' => $temp['name'],
                'category' => $temp['cat'],
                'content' => $temp['content'],
                'placeholders' => $temp['placeholders'],
                'is_active' => true,
            ]);
            $templates->push($t);
        }

        // 2. Create 3 Automations
        $automationsData = [
            ['name' => 'Otomasi Pengingat Tagihan SPP', 'trigger' => 'billing_due_3_days', 'temp_id' => $templates[0]->id],
            ['name' => 'Otomasi Tunggakan SPP', 'trigger' => 'billing_overdue', 'temp_id' => $templates[0]->id],
            ['name' => 'Otomasi Ketidakhadiran Siswa', 'trigger' => 'attendance_absent', 'temp_id' => $templates[3]->id],
        ];

        foreach ($automationsData as $auto) {
            WhatsappAutomation::create([
                'name' => $auto['name'],
                'trigger' => $auto['trigger'],
                'template_id' => $auto['temp_id'],
                'is_active' => true,
                'config' => ['delay_seconds' => 0, 'retry_limit' => 3],
            ]);
        }

        // 3. Create 3 Broadcast Histories
        $adminId = User::whereIn('role', ['admin', 'superadmin'])->first()?->id ?: 1;

        $broadcasts = [
            [
                'title' => 'Siaran Pengingat SPP Semester Genap',
                'temp_id' => $templates[0]->id,
                'msg' => 'Pesan pengingat pembayaran SPP dikirim serentak.',
                'recipients' => 50,
                'sent' => 48,
                'failed' => 2,
                'status' => 'completed',
            ],
            [
                'title' => 'Siaran Undangan Rapat Wali Kelas X',
                'temp_id' => $templates[3]->id,
                'msg' => 'Pesan undangan rapat wali murid dikirim serentak.',
                'recipients' => 40,
                'sent' => 40,
                'failed' => 0,
                'status' => 'completed',
            ],
            [
                'title' => 'Siaran Promosi Kegiatan Pondok Ramadhan',
                'temp_id' => $templates[4]->id,
                'msg' => 'Pesan promosi dikirim serentak.',
                'recipients' => 120,
                'sent' => 115,
                'failed' => 5,
                'status' => 'completed',
            ],
        ];

        foreach ($broadcasts as $index => $bc) {
            WhatsappBroadcast::create([
                'template_id' => $bc['temp_id'],
                'title' => $bc['title'],
                'message' => $bc['msg'],
                'target_type' => 'all_parents',
                'target_ids' => [1, 2, 3, 4, 5],
                'total_recipients' => $bc['recipients'],
                'sent_count' => $bc['sent'],
                'failed_count' => $bc['failed'],
                'status' => $bc['status'],
                'scheduled_at' => now()->subDays(10 - $index),
                'sent_at' => now()->subDays(10 - $index)->addMinutes(15),
                'created_by' => $adminId,
            ]);
        }

        // 4. Create 15 WhatsApp Logs (sent vs failed)
        for ($i = 0; $i < 12; $i++) {
            WhatsappLog::create([
                'recipient' => '628'.$faker->numerify('#########'),
                'message' => 'Simulasi pengiriman pesan otomatis sukses #'.($i + 1),
                'type' => $faker->randomElement(['keuangan', 'akademik', 'umum']),
                'status' => 'sent',
                'payload' => ['simulation' => true, 'fonnte_id' => $faker->uuid],
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            WhatsappLog::create([
                'recipient' => '628'.$faker->numerify('####999###'), // contains 999 to simulate sandbox fail
                'message' => 'Simulasi pengiriman pesan otomatis gagal #'.($i + 1),
                'type' => 'keuangan',
                'status' => 'failed',
                'error_message' => 'Simulated WhatsApp Gateway delivery failure (Sandbox Mode).',
                'payload' => ['simulation' => true, 'error' => 'API Timeout'],
            ]);
        }
    }
}
