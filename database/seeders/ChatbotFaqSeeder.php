<?php

namespace Database\Seeders;

use App\Models\ChatbotFaq;
use Illuminate\Database\Seeder;

class ChatbotFaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Bagaimana cara input nilai siswa?',
                'answer' => 'Guru dapat memasukkan nilai siswa dengan membuka menu **Input Nilai Siswa** (atau navigasi langsung ke /grades), memilih Tahun Akademik, Kelas, dan Mata Pelajaran aktif, kemudian mengisi formulir nilai tugas, ulangan harian, PTS, dan PAS.',
                'category' => 'akademik',
                'keywords' => ['nilai', 'input', 'guru', 'rapor', 'grade'],
            ],
            [
                'question' => 'Bagaimana cara orang tua membayar SPP sekolah?',
                'answer' => 'Orang tua/wali murid dapat masuk ke **Portal Wali Murid**, membuka menu **Tagihan SPP**, memilih tagihan yang aktif, lalu mengklik tombol **Checkout**. Pembayaran dapat diselesaikan menggunakan Virtual Account Bank (BCA, BNI, BRI, Mandiri) atau scan QRIS.',
                'category' => 'keuangan',
                'keywords' => ['bayar', 'spp', 'uang', 'keuangan', 'tagihan', 'checkout', 'midtrans'],
            ],
            [
                'question' => 'Bagaimana jika saya keluar dari fullscreen saat ujian CBT?',
                'answer' => 'Sistem CBT SISTA dilengkapi dengan proteksi **Fullscreen Blocker**. Jika Anda keluar dari mode layar penuh atau berpindah tab browser, sistem akan mencatatnya sebagai 1 kali pelanggaran (*violation*). Jika melampaui toleransi, ujian Anda akan otomatis ter-submit dan terkunci!',
                'category' => 'teknis',
                'keywords' => ['cbt', 'fullscreen', 'ujian', 'curang', 'pindah', 'tab', 'blokir'],
            ],
            [
                'question' => 'Bagaimana cara mengubah tema ke Dark Mode?',
                'answer' => 'SISTA mendukung penuh mode gelap global. Klik tombol toggle ikon **Matahari/Bulan** di bagian pojok kanan atas navigation bar untuk beralih antara Light Mode dan Dark Mode secara instan.',
                'category' => 'umum',
                'keywords' => ['dark', 'light', 'tema', 'warna', 'gelap', 'terang', 'tampilan'],
            ],
            [
                'question' => 'Bagaimana cara mengajukan cuti mengajar bagi guru?',
                'answer' => 'Guru dapat membuka portal **My HRD**, lalu masuk ke formulir **Pengajuan Cuti**. Setelah diajukan, permohonan Anda akan secara otomatis masuk ke rantai persetujuan berjenjang (Workflow Engine) untuk ditinjau oleh Waka Kurikulum dan Kepala Sekolah.',
                'category' => 'teknis',
                'keywords' => ['cuti', 'hrd', 'absen', 'izin', 'workflow', 'persetujuan'],
            ],
        ];

        foreach ($faqs as $faq) {
            ChatbotFaq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'category' => $faq['category'],
                    'keywords' => $faq['keywords'],
                    'is_active' => true,
                ]
            );
        }
    }
}
