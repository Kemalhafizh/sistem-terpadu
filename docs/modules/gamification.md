# Modul Gamifikasi & Engagement System Siswa SISTA

Modul Gamifikasi & Engagement System Siswa dirancang khusus untuk meningkatkan motivasi, partisipasi aktif, dan konsistensi belajar siswa di platform **SISTA (Sistem Informasi Sekolah Terpadu)**. Sistem ini bekerja secara real-time dan terintegrasi dengan berbagai sub-modul operasional sekolah, mulai dari kehadiran presensi, pengerjaan tugas E-Learning, perolehan nilai CBT, hingga amalan ibadah (Tahfidz dan Mutaba'ah Yaumi).

---

## 📊 Skema Database & Relasi

Sistem ini didukung oleh tiga tabel utama yang terisolasi secara aman di dalam skema database tenant sekolah masing-masing:

### 1. `achievement_definitions`
Menyimpan definisi master lencana pencapaian (badges) yang didukung sistem.
* `code` (string, unique): Kode unik lencana (contoh: `perfect_week`, `tahfidz_juz`).
* `name` (string): Nama lencana yang ditampilkan.
* `description` (text): Deskripsi dan syarat pembukaan lencana.
* `icon` (string): Karakter Emoji representatif lencana.
* `color` (string): Kode warna heksadesimal untuk aksen tampilan visual.
* `category` (string): Kategori (`akademik`, `kehadiran`, `sosial`, `keislaman`, `khusus`).
* `rarity` (string): Tingkat kelangkaan lencana (`common`, `rare`, `epic`, `legendary`).
* `xp_reward` (integer): Bonus XP yang diberikan saat lencana berhasil dibuka.
* `criteria` (json): Konfigurasi syarat otomatis evaluasi (contoh: `{"type":"attendance_streak","value":5}`).
* `is_auto` (boolean): Apakah lencana dievaluasi secara otomatis oleh mesin.

### 2. `student_achievements`
Mencatat lencana yang berhasil diraih oleh siswa aktif.
* `student_id` (foreign key): Menghubungkan ke tabel `students`.
* `achievement_id` (foreign key): Menghubungkan ke tabel `achievement_definitions`.
* `earned_at` (date): Tanggal perolehan lencana.
* `context` (text): Keterangan kronologi pemenuhan syarat saat lencana terbuka.

### 3. `student_xp_logs`
Buku kas mutasi perolehan XP siswa untuk auditabilitas.
* `student_id` (foreign key): Menghubungkan ke tabel `students`.
* `xp_amount` (integer): Jumlah XP yang bertambah.
* `source` (string): Modul pemicu (`attendance`, `assignment`, `forum`, `achievement`, `tahfidz`, `mutabaah`, `exam`).
* `description` (string): Kronologi detail alasan penambahan XP.

---

## 🧠 Aturan Bisnis & Logika Leveling

Level siswa dihitung secara dinamis melalui kolom `total_xp` pada tabel `students` menggunakan Laravel Accessor:

```php
// Rumus Kalkulasi Level (Tiap Kelipatan 100 XP)
public function getLevelAttribute(): int
{
    return 1 + (int) floor($this->total_xp / 100);
}

// Rumus Progress Bar Menuju Level Berikutnya
public function getXpProgressPercentAttribute(): float
{
    $currentLevelXp = (int) floor($this->total_xp / 100) * 100;
    $earnedXpInLevel = $this->total_xp - $currentLevelXp;
    return round(($earnedXpInLevel / 100) * 100, 1);
}
```

---

## ⚡ Callback Pemicu (Service Triggers)

Mesin gamifikasi mengekspos metode pemicu terpusat melalui `App\Services\GamificationService`. Metode ini dapat diinjeksikan secara modular pada berbagai controller di SISTA:

| Metode Pemicu | Parameter | Tambahan XP | Penjelasan |
| :--- | :--- | :---: | :--- |
| `updateAttendanceStreak($student, 'H')` | `Student $student`, `string $status` | **+10 XP** | Menaikkan `current_streak` dan mengevaluasi lencana streak. |
| `updateAttendanceStreak($student, 'A')` | `Student $student`, `string $status` | **0 XP** | Mereset `current_streak` siswa menjadi `0` akibat Alpa. |
| `onAssignmentSubmitted($student)` | `Student $student` | **+15 XP** | Pemicu saat siswa mengunggah tugas di E-Learning. |
| `onForumReplyPosted($student)` | `Student $student` | **+5 XP** | Pemicu saat berpartisipasi menjawab di Forum Akademik. |
| `onExamCompleted($student, $score)` | `Student $student`, `float $score` | **+50 XP** atau **+20 XP** | **+50 XP** jika nilai CBT >= 80, selain itu **+20 XP**. |
| `onTahfidzRecorded($student)` | `Student $student` | **+30 XP** | Pemicu saat menyelesaikan setoran hafalan Al-Quran. |
| `onMutabaahCompleted($student)` | `Student $student` | **+20 XP** | Pemicu saat mengisi catatan ibadah mutaba'ah harian. |

---

## 🎨 Katalog 12 Lencana Otomatis Sultan Agung

Sistem melacak pembukaan lencana secara otomatis (auto-evaluasi) setiap kali XP ditambahkan:

1. **Hari Pertama (`first_day`):** Terbuka saat login pertama kali (XP >= 5). Common (+5 XP).
2. **Minggu Sempurna (`perfect_week`):** Hadir penuh 5 hari berturut-turut. Common (+15 XP).
3. **Bulan Sempurna (`perfect_month`):** Hadir penuh 20 hari berturut-turut. Rare (+50 XP).
4. **Streak 30 Hari (`streak_30`):** Hadir penuh 30 hari berturut-turut. Epic (+100 XP).
5. **Juara Kelas (`top_scorer`):** Meraih total minimal 1000 XP. Rare (+75 XP).
6. **Kutu Buku (`bookworm`):** Meraih total minimal 500 XP. Rare (+40 XP).
7. **Penolong Forum (`forum_helper`):** Menulis 20 balasan forum diskusi. Rare (+60 XP).
8. **Hafiz Juz Al-Quran (`tahfidz_juz`):** Setoran tahfidz Al-Quran sebanyak 10 kali. Legendary (+200 XP).
9. **Istiqomah Ibadah (`mutabaah_streak`):** Mengisi jurnal mutaba'ah sebanyak 30 hari. Epic (+120 XP).
10. **Ace Ujian CBT (`quiz_ace`):** Meraih total minimal 1500 XP. Epic (+80 XP).
11. **Siswa Terajin (`early_bird`):** Meraih total minimal 800 XP. Common (+20 XP).
12. **Sholat Champion (`sholat_champion`):** Mutaba'ah yaumiyah lengkap sebanyak 40 hari. Legendary (+150 XP).

---

## 🧪 Pengujian Otomatis

Seluruh logika gamifikasi telah terproteksi secara penuh oleh rangkaian uji coba Pest PHP yang aman dari regresi kode di masa mendatang:

```bash
# Menjalankan pengujian fitur gamifikasi
vendor/bin/pest tests/Feature/GamificationTest.php
```

Uji coba memvalidasi keamanan rute hak akses siswa, kalkulasi matematis level, kenaikan streak presensi kelas, pencatatan log mutasi XP, evaluasi otomatis badges, serta ketepatan pengurutan podium papan peringkat (leaderboard).
