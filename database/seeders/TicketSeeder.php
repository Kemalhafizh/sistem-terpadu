<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $stafTu = User::where('email', 'staf.tu@sultanagung1.sch.id')->first();
        $wakaSarpras = User::where('email', 'waka.sarpras@sultanagung1.sch.id')->first();

        $reporters = User::whereIn('role', ['guru', 'student', 'parent'])->get();

        Ticket::truncate();
        TicketComment::truncate();

        $ticketsData = [
            [
                'title' => 'Koneksi Wi-Fi Ruang Guru Tidak Bisa Terhubung',
                'description' => 'Mohon bantuan tim IT, Wi-Fi di ruang guru lantai 2 tidak bisa tersambung sejak pagi ini. Muncul keterangan invalid password.',
                'category' => 'it',
                'priority' => 'high',
                'status' => 'in_progress',
                'assignee' => $admin,
                'comments' => [
                    ['user' => $admin, 'body' => 'Sedang dicek ke router akses point ruang guru.', 'internal' => true],
                    ['user' => $admin, 'body' => 'Mohon dicoba kembali menggunakan password baru: SultanAgung2026.', 'internal' => false],
                ]
            ],
            [
                'title' => 'Kerusakan Air Conditioner (AC) Kelas XI-A',
                'description' => 'AC di kelas XI-A mengeluarkan bunyi bising dan tidak dingin, mengganggu jalannya kegiatan belajar mengajar.',
                'category' => 'sarpras',
                'priority' => 'medium',
                'status' => 'open',
                'assignee' => $wakaSarpras,
                'comments' => []
            ],
            [
                'title' => 'Kesulitan Akses Pembayaran SPP Online',
                'description' => 'Saya mencoba bayar SPP anak saya lewat bank transfer Mandiri tapi keterangannya tagihan tidak ditemukan.',
                'category' => 'keuangan',
                'priority' => 'high',
                'status' => 'resolved',
                'assignee' => $stafTu,
                'resolution' => 'Masalah diselesaikan dengan sinkronisasi ulang ID billing di gerbang Midtrans. Tagihan terkonfirmasi lunas.',
                'comments' => [
                    ['user' => $stafTu, 'body' => 'Mohon lampirkan screenshoot kendala / nomor VA yang digunakan.', 'internal' => false],
                    ['user' => $stafTu, 'body' => 'Data billing sudah disinkronkan ulang.', 'internal' => true],
                ]
            ],
            [
                'title' => 'E-learning Error: Gagal Upload File Tugas Tugas Mandiri',
                'description' => 'Setiap mencoba upload file tugas fisika format PDF 4MB selalu loading lama lalu error 500.',
                'category' => 'akademik',
                'priority' => 'medium',
                'status' => 'closed',
                'assignee' => $admin,
                'resolution' => 'Limit upload file di server PHP config dinaikkan dari 2MB menjadi 10MB.',
                'comments' => [
                    ['user' => $admin, 'body' => 'Cek konfigurasi php.ini upload_max_filesize.', 'internal' => true],
                    ['user' => $admin, 'body' => 'Konfigurasi server telah diperbarui. Batas upload file sekarang 10MB. Silakan dicoba kembali.', 'internal' => false],
                ]
            ],
            [
                'title' => 'Meja & Kursi Rusak Kelas X-B',
                'description' => 'Ada 2 set meja dan kursi siswa yang goyang dan kayunya retak, mohon diganti demi keselamatan siswa.',
                'category' => 'sarpras',
                'priority' => 'low',
                'status' => 'in_progress',
                'assignee' => $wakaSarpras,
                'comments' => [
                    ['user' => $wakaSarpras, 'body' => 'Sudah dipindahkan ke gudang perbaikan dan disiapkan meja pengganti.', 'internal' => true],
                ]
            ],
            [
                'title' => 'Kesalahan Ketik Nama Siswa di Cetak Raport',
                'description' => 'Nama anak saya di rapor tertera "Muhammad Rizki" seharusnya "Muhammad Rizky Pratama". Mohon diperbaiki di database.',
                'category' => 'akademik',
                'priority' => 'medium',
                'status' => 'resolved',
                'assignee' => $stafTu,
                'resolution' => 'Nama siswa telah diperbarui di database SISTA sesuai akta kelahiran.',
                'comments' => [
                    ['user' => $stafTu, 'body' => 'Silakan kirim foto akta kelahiran anak ke WA Tata Usaha.', 'internal' => false],
                ]
            ],
            [
                'title' => 'LCD Proyektor Ruang Aula Mati Total',
                'description' => 'LCD proyektor di aula tidak mau menyala sama sekali saat dihubungkan ke aliran listrik, padahal besok ada rapat wali murid.',
                'category' => 'sarpras',
                'priority' => 'critical',
                'status' => 'resolved',
                'assignee' => $wakaSarpras,
                'resolution' => 'Kabel power LCD proyektor diganti baru dan sekarang berfungsi normal.',
                'comments' => [
                    ['user' => $wakaSarpras, 'body' => 'Beli kabel power LCD baru di toko komputer terdekat.', 'internal' => true],
                ]
            ],
            [
                'title' => 'Permintaan Reset Password Akun E-Learning',
                'description' => 'Saya lupa password akun e-learning saya dan tidak bisa login kembali. Mohon bantuan reset password.',
                'category' => 'it',
                'priority' => 'low',
                'status' => 'open',
                'assignee' => $admin,
                'comments' => []
            ],
            [
                'title' => 'Stok Buku Paket Fisika Kurikulum Merdeka Kurang',
                'description' => 'Buku paket fisika kelas X di perpustakaan kurang 5 buah untuk dibagikan ke siswa kelas X-B.',
                'category' => 'sarpras',
                'priority' => 'medium',
                'status' => 'open',
                'assignee' => $wakaSarpras,
                'comments' => []
            ],
            [
                'title' => 'Double Billing / Tagihan Uang OSIS Ganda',
                'description' => 'Di dashboard muncul tagihan Uang OSIS sebanyak dua kali. Mohon dihapus salah satunya.',
                'category' => 'keuangan',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assignee' => $stafTu,
                'comments' => [
                    ['user' => $stafTu, 'body' => 'Sedang memeriksa database log billing Uang OSIS.', 'internal' => true],
                ]
            ],
        ];

        foreach ($ticketsData as $idx => $tData) {
            $reporter = $reporters->random();
            $issueDate = now()->subDays(rand(1, 10));

            $ticket = Ticket::create([
                'uuid' => (string) Str::uuid(),
                'title' => $tData['title'],
                'description' => $tData['description'],
                'category' => $tData['category'],
                'priority' => $tData['priority'],
                'status' => $tData['status'],
                'reporter_id' => $reporter->id,
                'assignee_id' => $tData['assignee']?->id,
                'resolution' => $tData['resolution'] ?? null,
                'resolved_at' => in_array($tData['status'], ['resolved', 'closed']) ? (clone $issueDate)->addHours(rand(2, 48)) : null,
                'closed_at' => $tData['status'] === 'closed' ? (clone $issueDate)->addHours(rand(48, 72)) : null,
                'created_at' => $issueDate,
            ]);

            // Add comments
            foreach ($tData['comments'] as $comm) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $comm['user']->id,
                    'body' => $comm['body'],
                    'is_internal' => $comm['internal'],
                ]);
            }
        }
    }
}
