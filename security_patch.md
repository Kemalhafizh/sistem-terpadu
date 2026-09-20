# 🛡️ Laporan Keamanan & Kualitas Kode: security_patch.md
**Project:** Sistem Terpadu (SISTA) - SMA Islam Sultan Agung 1 Semarang
**Tanggal Review Terbaru:** 19 September 2026 (Audit Komprehensif Fase 98 - 114, Mobile Gateway & QC Standards)

Dokumen ini merangkum temuan audit keamanan dan kualitas kode (*code review*) terhadap fungsionalitas baru yang baru saja diimplementasikan. Fokus utama audit adalah mendeteksi *edge cases*, kegagalan validasi input, cacat logika bisnis (*business logic flaws*), serta celah keamanan tingkat menengah hingga kritis.

---

## 📋 Ringkasan Eksekutif Temuan

| No | Komponen / Fungsi | Deskripsi Celah Keamanan / Bug | Tingkat Risiko | Status |
| :--- | :--- | :--- | :---: | :---: |
| 1 | `CmsController::mediaLibrary` | *Arbitrary File Deletion* via *Path Traversal* (`delete` parameter) | **Kritis (High)** | **Selesai ✅** |
| 2 | `CmsController::mediaLibrary` | Stored XSS via SVG Upload / File Upload Spoofing | **Tinggi (Medium-High)** | **Selesai ✅** |
| 3 | `SmartAlertController::escalate` | *Broken Access Control* (Tidak ada otorisasi Gate/Role pada eskalasi) | **Tinggi (Medium-High)** | **Selesai ✅** |
| 4 | `WorkflowController::delegate` | *Self-Approval / Separation of Duties Bypass* (Delegasi ke Pengaju) | **Sedang (Medium)** | **Selesai ✅** |
| 5 | `ChatbotController::importFaqs` | *Denial of Service* (DoS) via Memory Exhaustion & Crash via Long Query | **Sedang (Medium)** | **Selesai ✅** |
| 6 | `WorkflowController::analytics` | Kueri Raw SQL tidak portabel (Menggunakan fungsi khusus SQLite `strftime`) | **Rendah (Low/Bug)** | **Selesai ✅** |
| 7 | `UksController::storeScreeningResults` | Ketiadaan validasi rentang batas nilai (*Input Range/Bound Validation*) | **Rendah (Low/Bug)** | **Selesai ✅** |
| 8 | `ElearningController` | *Broken Object Level Authorization* (BOLA) pada akses kelas virtual, materi, dan komentar | **Tinggi (Medium-High)** | **Selesai ✅** |
| 9 | `ElearningController::storeComment` | Stored XSS pada komentar materi diskusi e-learning | **Sedang (Medium)** | **Selesai ✅** |
| 10 | `ElearningController` | *Insecure Direct Object Reference* (IDOR) pada penulisan materi/tugas/pengumuman oleh Guru lain | **Tinggi (Medium-High)** | **Selesai ✅** |
| 11 | `SpmbController::printCard` | IDOR/BOLA publik pada cetak kartu pendaftaran peserta | **Tinggi (Medium-High)** | **Selesai ✅** |
| 12 | `ImportExportController::execute` | *Path Traversal / Arbitrary File Reading* pada parameter `file_path` | **Kritis (High)** | **Selesai ✅** |
| 13 | `MidtransWebhookController` | Webhook Signature Bypass / Payment Spoofing via parameter `is_simulation` | **Kritis (High)** | **Selesai ✅** |
| 14 | `UatFeedbackController::store` | Arbitrary File Upload / Remote Code Execution via Base64 Image | **Kritis (Critical)** | **Selesai ✅** |
| 15 | `MobileAuthController::biometricVerify` | Authentication Bypass via Missing Signature & Nonce Verification | **Kritis (Critical)** | **Selesai ✅** |
| 16 | `ApiTeacherController::uploadExamImage` | Stored XSS via SVG Image Upload pada Bank Soal CBT | **Tinggi (High)** | **Selesai ✅** |
| 17 | `ApiStudentController::cbtExamQuestions` | Broken Access Control & Exam Leakage (Soal diakses sebelum jadwal) | **Tinggi (High)** | **Selesai ✅** |
| 18 | `ApiStudentController::cbtSubmitExam` | Cacat Logika Bisnis: Resubmisi Berulang & Skor Ujian Ditimpa | **Tinggi (High)** | **Selesai ✅** |
| 19 | `CbtEnterpriseApiController` | BOLA / IDOR pada Intervensi Proctor (Force Submit / Extend Time) | **Tinggi (High)** | **Selesai ✅** |
| 20 | `GeofenceAttendanceService` | Replay Attack pada Verifikasi Token Dynamic TOTP QR Presensi | **Sedang (Medium)** | **Selesai ✅** |
| 21 | `CbtAntiCheatEngine::unlockExamWithSupervisorPin` | CBT Anti-Cheat Unlock Bypass via Wildcard 6-Digit PIN Logic | **Kritis (Critical)** | **Selesai ✅** |
| 22 | `AndroidManifest.xml` & `network_security_config.xml` | Cleartext HTTP Permitted & User CA Trust Injection (MITM & Backup Risk) | **Tinggi (High)** | **Selesai ✅** |
| 23 | `DeepLinkRouter::resolveRoute` | Deep Link Domain Substring Spoofing via `host.contains()` | **Tinggi (High)** | **Selesai ✅** |
| 24 | `ApiClient` | Sensitive Bearer Token & Credential Leakage via Full Body Logcat | **Sedang (Medium)** | **Selesai ✅** |
| 25 | `eds/comparison.blade.php` | Stored/Reflected XSS dalam Tag `<script>` via Raw Unescaped Blade Output | **Tinggi (High)** | **Selesai ✅** |
| 26 | `admin/chatbot/conversation-detail.blade.php` | Stored XSS via Bot Response Raw Echo di Admin Panel | **Sedang (Medium)** | **Selesai ✅** |
| 27 | `StudentProfileApiController` | Massive IDOR / BOLA pada Profil Siswa 360 Derajat (Akademik/Ibadah/Disiplin/Medis) | **Kritis (Critical)** | **Selesai ✅** |
| 28 | `SchoolOperationsApiController` | BOLA pada Rekam Medis UKS & Privilege Escalation Input Kunjungan UKS | **Kritis (Critical)** | **Selesai ✅** |
| 29 | `routes/api.php (counseling & journals)` | Broken Access Control pada Bimbingan Konseling (BK) & Jurnal Guru | **Tinggi (High)** | **Selesai ✅** |
| 30 | `routes/api.php (biometric)` | Ketiadaan Rate Limiting pada Tantangan & Verifikasi Biometrik (Brute Force / DoS) | **Sedang (Medium)** | **Selesai ✅** |
| 31 | `AdminMobileDashboardController` & `routes/api.php` | Broken Access Control & Privilege Escalation pada Dashboard, Revenue KPI, dan Workflow Approval | **Kritis (Critical)** | **Selesai ✅** |
| 32 | `ParentPortalController` & `routes/api.php` | Massive IDOR / BOLA pada Aktivitas Harian, Rapor Mingguan & Tolok Ukur Nilai Anak | **Kritis (Critical)** | **Selesai ✅** |
| 33 | `VizController` | Insecure Direct Object Reference (BOLA) pada Radar Psikometrik & Tren Nilai Siswa | **Tinggi (High)** | **Selesai ✅** |
| 34 | `LibraryMobileApiController::selfCheckout` | Insecure Direct Object Reference (IDOR) Peminjaman Buku via Parameter `student_id` | **Tinggi (High)** | **Selesai ✅** |
| 35 | `AchievementMobileApiController::verify` | Insecure Role Blacklist Bypass pada Verifikasi Prestasi Siswa & Poin Portofolio | **Tinggi (High)** | **Selesai ✅** |
| 36 | `PwaController::subscribe` & `routes/web.php` | Unauthenticated Web Push Hijacking & Insecure Super Admin Default Fallback | **Kritis (Critical)** | **Selesai ✅** |
| 37 | `DocumentMobileApiController::ocrScan` | Unrestricted File Upload & Stored XSS via SVG Upload pada Direktori Publik | **Tinggi (High)** | **Selesai ✅** |
| 38 | `EvaluationService::submitTeacherEvaluation` | Cacat Logika Anti-Spam / Bypass Pencegahan Double Submission pada Survei 360 Anonim | **Sedang (Medium)** | **Selesai ✅** |
| 39 | `UtbkMobileApiController` | Unhandled Null Pointer Dereference pada Profil Non-Siswa (500 Server Error) | **Rendah (Low/Bug)** | **Selesai ✅** |
| 40 | `DocumentMobileApiController::submitSignature` | Ketiadaan Batas Ukuran String Base64 Tanda Tangan Digital (Database Bloat / DoS) | **Sedang (Medium)** | **Selesai ✅** |
| 41 | `CbtAntiCheatEngine.kt` (Android) | Hardcoded Plain-Text Supervisor Bypass PINs pada CBT Anti-Cheat Engine | **Tinggi (High)** | **Selesai ✅** |
| 42 | `SsoWebViewScreen.kt` (Android) | Insecure WebView File Access & Open Redirect / Host Interception pada Portal SSO | **Tinggi (High)** | **Selesai ✅** |
| 43 | `CbtLatexMathView.kt` (Android) | Script Tag Breakout / Stored XSS pada Embedded KaTeX WebView Renderer | **Sedang (Medium)** | **Selesai ✅** |
| 44 | `CbtEnterpriseApiController` & `CbtEnterpriseService` | CBT Attempt State Machine Reversion & Missing Take Gate Authorization | **Kritis (Critical)** | **Selesai ✅** |
| 45 | `SsrfGuardService` & Webhook Dispatchers | Server-Side Request Forgery (SSRF) & Cloud Metadata Pivoting pada Webhook | **Tinggi (High)** | **Selesai ✅** |
| 46 | `MidtransWebhookController` | Cryptographic Timing Attack, Empty Secret Bypass & Zero-Amount Tampering | **Tinggi (High)** | **Selesai ✅** |
| 47 | `SyncController` & `OfflineSyncService` | Unbounded Action Batch DoS & Missing Cross-Tenant User Context Binding | **Tinggi (High)** | **Selesai ✅** |
| 48 | `ImpersonateController` & `RestrictImpersonation` | Session Fixation & Administrative Privilege Leak saat Account Impersonation | **Sedang (Medium)** | **Selesai ✅** |
| 49 | `KeystoreManager.kt` (Android) | Biometric Challenge Hash Bypass & Missing Hardware Asymmetric Signing | **Tinggi (High)** | **Selesai ✅** |

### 🧪 Ringkasan Eksekutif Audit Kualitas Kode (Quality Control & Architectural Governance)

| No | Komponen / Berkas | Temuan Quality Control & Standard | Tindakan Remediasi | Status |
| :--- | :--- | :--- | :--- | :---: |
| QC-1 | `views`, `controllers`, `services`, `seeders` | Pelanggaran Standar Tata Kelola Kode: Emoji Unicode ditemukan pada 8 berkas (`NoEmojiGovernanceTest`) | Seluruh emoji Unicode diganti dengan SVG icons, teks formal, dan identifier string | **Selesai ✅** |
| QC-2 | `public/manifest.json` | Inkonsistensi metadata spesifikasi PWA (`short_name` & `theme_color` tidak sinkron dengan `PwaTest`) | Diselaraskan kembali ke `"SISTA"` dan `"#0d9488"` | **Selesai ✅** |
| QC-3 | `NotificationPreference.php` | Ketiadaan casting eksplisit boolean (`channel_*`) memicu kegagalan evaluasi SQLite (`NotificationCenterTest`) | Menambahkan casting eksplisit `boolean` pada model Eloquent | **Selesai ✅** |
| QC-4 | `database/factories/LibraryBookFactory.php` | Inkonsistensi Enum (`pdf`, `video` vs `physical`, `digital`) memicu SQLite CHECK constraint failure | Menyelaraskan nilai generator randomElement dengan skema migrasi tabel | **Selesai ✅** |

---

## 🔍 Analisis Baris per Baris & Detail Kerentanan

### 1. Arbitrary File Deletion / Path Traversal di `CmsController::mediaLibrary`
* **Lokasi File:** [CmsController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/CmsController.php#L407-L417)
* **Baris Kode Asal:**
  ```php
  if ($request->has('delete')) {
      $file = $request->delete;
      if (str_starts_with($file, 'cms/') && Storage::disk('public')->exists($file)) {
          Storage::disk('public')->delete($file);
          return redirect()->route('cms.media.index')->with('success', 'File berhasil dihapus dari media library.');
      }
  }
  ```
* **Analisis Celah Keamanan:**
  - Fungsi ini menerima input `$file` dari parameter request `delete` yang sepenuhnya dikontrol oleh pengguna.
  - Pengecekan `str_starts_with($file, 'cms/')` hanya memvalidasi apakah string dimulai dengan teks `cms/`. Cek ini **tidak membersihkan** atau **tidak menyaring** pola *directory traversal* seperti `../`.
  - Jika seorang penyerang dengan hak akses CMS mengirim parameter `delete=cms/../../../../.env`, kondisi `str_starts_with` akan bernilai `true`.
  - Pustaka Flysystem lokal Laravel akan menyelesaikan jalur relatif tersebut menjadi `storage/app/public/cms/../../../../.env` (yaitu file `.env` di root proyek). Hal ini memungkinkan penghapusan file konfigurasi sensitif (seperti `.env`, database SQLite, atau kode sumber aplikasi), yang memicu kerusakan sistem (*Denial of Service*).
* **Solusi Perbaikan:**
  Sanitasi input menggunakan `basename()` untuk mengambil nama file asli saja, atau tolak jalur yang mengandung karakter `..` atau `/`.

---

### 2. Inadequate File Upload Validation di `CmsController::mediaLibrary`
* **Lokasi File:** [CmsController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/CmsController.php#L399-L405)
* **Baris Kode Asal:**
  ```php
  if ($request->isMethod('post')) {
      $request->validate([
          'file' => 'required|file|image|max:5120',
      ]);
      $path = $request->file('file')->store('cms/media', 'public');
      return redirect()->route('cms.media.index')->with('success', 'File berhasil diupload ke media library.');
  }
  ```
* **Analisis Celah Keamanan:**
  - Validasi `'file' => 'required|file|image|max:5120'` mendeteksi tipe file menggunakan deteksi MIME type bawaan Laravel.
  - Secara default, aturan `image` meloloskan file berformat **SVG** (`image/svg+xml`). File SVG adalah dokumen berbasis XML yang dapat disisipi kode JavaScript jahat melalui tag `<script>` atau atribut seperti `onload`.
  - Jika pengguna mengunggah berkas SVG berbahaya, berkas tersebut akan disimpan di direktori publik. Ketika admin atau pengguna lain mengakses berkas SVG secara langsung di peramban, JavaScript penyerang akan dieksekusi dalam konteks sesi korban, memicu kerentanan **Stored Cross-Site Scripting (XSS)**.
  - Selain itu, tidak adanya batasan ekstensi yang ketat (`mimes:jpeg,png,jpg,webp`) dapat meloloskan berkas berbahaya ber-ekstensi ganda (misalnya `shell.php.png`) yang pada konfigurasi web server tertentu (seperti Apache MultiViews) tetap dapat dieksekusi sebagai script PHP.
* **Solusi Perbaikan:**
  Batasi jenis ekstensi berkas secara eksplisit dengan aturan `mimes` (contoh: `mimes:jpeg,png,jpg,webp,gif`) dan hindari menerima SVG kecuali melalui sanitasi XML yang ketat.

---

### 3. Broken Access Control di `SmartAlertController::escalate`
* **Lokasi File:** [SmartAlertController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/SmartAlertController.php#L313-L325)
* **Baris Kode Asal:**
  ```php
  public function escalate(SmartAlert $alert, Request $request)
  {
      $request->validate([
          'supervisor_id' => 'required|exists:users,id',
          'escalation_notes' => 'required|string|min:5',
      ]);

      $supervisor = User::findOrFail($request->supervisor_id);

      $this->service->escalateAlert($alert, $supervisor, $request->escalation_notes);

      return redirect()->route('smart-alerts.index')->with('success', 'Smart Alert berhasil dieskalasikan ke ' . $supervisor->name);
  }
  ```
* **Analisis Celah Keamanan:**
  - Metode `escalate` **sama sekali tidak** melakukan pemeriksaan otorisasi. Tidak ada pemanggilan Gate (`Gate::authorize`) maupun validasi kecocokan role pengguna.
  - Sebagai perbandingan, metode lain seperti `acknowledge` (baris 274) menggunakan `Gate::authorize('acknowledge', $alert)` dan `resolve` (baris 291) menggunakan `Gate::authorize('resolve', $alert)`.
  - Karena tidak ada pengaman, pengguna mana pun (termasuk Guru dari kelas lain, staf rendahan, atau siapa pun yang memiliki akses ke middleware `auth`) dapat mengirim request POST ke endpoint eskalasi untuk merubah tingkat keparahan (severity) alert apa saja menjadi `critical`, menunjuk supervisor sepihak, dan memicu banjir spam pesan WhatsApp otomatis ke pejabat struktural sekolah (Kepala Sekolah, dll).
* **Solusi Perbaikan:**
  Tambahkan otorisasi otentik menggunakan policy Gate (misalnya `Gate::authorize('escalate', $alert)` atau batasi akses hanya untuk admin/staf BK).

---

### 4. Self-Approval & Cacat Logika Bisnis di `WorkflowController::delegate`
* **Lokasi File:** [WorkflowController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/WorkflowController.php#L320-L337) dan [WorkflowService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/WorkflowService.php#L225-L251)
* **Baris Kode Asal:**
  ```php
  // Di WorkflowController.php
  public function delegate(WorkflowRequest $workflowRequest, Request $request)
  {
      $currentApproval = $workflowRequest->currentApproval;
      if (!$currentApproval || $currentApproval->role !== auth()->user()->role) {
          abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
      }
      $request->validate([
          'delegatee_id' => 'required|exists:users,id',
          'remarks' => 'nullable|string',
      ]);
      $delegatee = User::findOrFail($request->delegatee_id);
      $this->service->delegate($workflowRequest, auth()->user(), $delegatee, $request->remarks);
      ...
  }
  ```
* **Analisis Celah Keamanan:**
  - **Self-Approval Risk (Bypass Separation of Duties):** Controller membolehkan delegasi langkah persetujuan kepada pengguna mana pun (`exists:users,id`). Jika seorang Approver mendelegasikan persetujuan ke user yang merupakan **Pengaju (Requester)** dari workflow tersebut, maka pengaju tersebut dapat menyetujui pengajuannya sendiri. Cacat logika bisnis ini merusak prinsip dasar *separation of duties*.
  - **Re-delegation Bypass:** Logika validasi hanya membandingkan role: `currentApproval->role !== auth()->user()->role`. Jika alur persetujuan saat ini bernilai role `waka_kurikulum` dan sudah didelegasikan khusus ke `User A` (`delegated_to_id = User A`), pengguna lain dengan role `waka_kurikulum` (`User B`) tetap lolos validasi ini dan dapat menimpa/mendelegasikan ulang alur tersebut ke orang lain secara sepihak.
* **Solusi Perbaikan:**
  Validasi agar `delegatee_id` tidak sama dengan `$workflowRequest->requester_id` (mencegah self-approval), dan pastikan hanya penerima delegasi aktif atau pengguna asli yang dapat melakukan delegasi ulang.

---

### 5. Memory Exhaustion & Crash di `ChatbotController::importFaqs`
* **Lokasi File:** [ChatbotController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ChatbotController.php#L212-L249)
* **Baris Kode Asal:**
  ```php
  $file = $request->file('csv_file');
  $csvData = file_get_contents($file->getRealPath());
  $rows = array_map('str_getcsv', explode("\n", $csvData));
  ...
  ChatbotFaq::create([
      'question' => $question,
      'answer' => $answer,
      ...
  ]);
  ```
* **Analisis Kualitas Kode:**
  - **Memory Exhaustion (DoS):** Penggunaan `file_get_contents` membaca seluruh file ke memori sekaligus. Jika pengguna mengunggah file CSV berukuran besar, memori PHP akan habis (*Out of Memory*), menyebabkan crash.
  - **Kesalahan Parsing CSV:** Melakukan `explode("\n", $csvData)` adalah pendekatan yang salah untuk memproses CSV. Jika suatu baris CSV mengandung karakter baris baru (`\n`) di dalam sel ber-tanda kutip (hal yang valid dalam standar RFC 4180), pemisahan baris ini akan merusak format baris data dan menyebabkan kesalahan database atau data rusak.
  - **Database Crash (Truncation Error):** Kolom database `question` biasanya bertipe `VARCHAR(255)`. Kode di atas langsung memasukkan `$question` hasil parse tanpa membatasi atau memotong panjang string. Jika baris CSV berisi pertanyaan melebihi 255 karakter, kueri database akan gagal dengan error *Data too long / String data, right truncated* di MySQL/Postgres.
* **Solusi Perbaikan:**
  Gunakan stream generator seperti `fopen()` dan `fgetcsv()` untuk membaca file baris demi baris secara hemat memori, tambahkan validasi ukuran string pertanyaan (`max:255`), dan lakukan sanitasi tag HTML sebelum disimpan.

---

### 6. SQLite Raw Query Dependency di `WorkflowController::analytics`
* **Lokasi File:** [WorkflowController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/WorkflowController.php#L427-L442)
* **Baris Kode Asal:**
  ```php
  $avgTimes = WorkflowRequest::whereIn('status', ['approved', 'rejected'])
      ->join('workflow_definitions', 'workflow_requests.definition_id', '=', 'workflow_definitions.id')
      ->select('workflow_definitions.name', DB::raw('avg(strftime("%s", workflow_requests.updated_at) - strftime("%s", workflow_requests.created_at)) / 3600 as avg_hours'))
      ...
  ```
* **Analisis Kualitas Kode:**
  - Fungsi SQLite `strftime("%s", ...)` digunakan di dalam `DB::raw()` untuk menghitung selisih waktu dalam detik.
  - Meskipun tes lokal berhasil karena menggunakan database SQLite, kueri raw SQL ini **tidak portabel** dan akan langsung **gagal (SQL Syntax Error 500)** di lingkungan produksi jika menggunakan MySQL (`TIMESTAMPDIFF` atau `UNIX_TIMESTAMP`) atau PostgreSQL (`EXTRACT(EPOCH FROM ...)`).
* **Solusi Perbaikan:**
  Gunakan penanggalan dinamis dengan memeriksa nama driver koneksi database (`config('database.default')` atau kueri pembantu Carbon secara kolektif di PHP memory setelah data diambil).

---

### 7. Input Range & Bound Validation di `UksController::storeScreeningResults`
* **Lokasi File:** [UksController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/UksController.php#L493-L538)
* **Baris Kode Asal:**
  ```php
  'results.*.height_cm' => 'nullable|numeric',
  'results.*.weight_kg' => 'nullable|numeric',
  ```
* **Analisis Kualitas Kode:**
  - Ketiadaan batasan nilai minimum dan maksimum (*Bound Validation*) pada validasi array hasil screening.
  - Dibandingkan dengan `updateHealthRecord` (baris 392) yang membatasi tinggi badan di antara 30 s.d 250 cm dan berat badan 5 s.d 200 kg, `storeScreeningResults` memperbolehkan input nilai tidak logis (seperti tinggi badan `-200 cm` atau `9999 cm`), yang merusak integritas data rekam medis siswa (*data integrity*).
* **Solusi Perbaikan:**
  Tambahkan aturan `min:30|max:250` dan `min:5|max:200` pada validasi parameter `height_cm` dan `weight_kg` di baris masukan array.

---

## 🛠️ Rekomendasi Patch Keamanan (Security Patches)

Berikut adalah usulan potongan kode perbaikan (*remediation patches*) untuk menutup celah-celah tersebut:

### Patch #1 & #2: Perbaikan Media Library di `CmsController.php`
```diff
     public function mediaLibrary(Request $request)
     {
         $this->authorizeCms();
         
         if ($request->isMethod('post')) {
             $request->validate([
-                'file' => 'required|file|image|max:5120',
+                // Batasi ekstensi gambar secara eksplisit dan tolak tipe berbahaya seperti SVG
+                'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp|max:5120',
             ]);
-            $path = $request->file('file')->store('cms/media', 'public');
+            // Simpan file dengan nama acak aman
+            $file = $request->file('file');
+            $safeName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
+            $path = $file->storeAs('cms/media', $safeName, 'public');
             return redirect()->route('cms.media.index')->with('success', 'File berhasil diupload ke media library.');
         }
 
         if ($request->has('delete')) {
-            $file = $request->delete;
-            if (str_starts_with($file, 'cms/') && Storage::disk('public')->exists($file)) {
-                Storage::disk('public')->delete($file);
-                return redirect()->route('cms.media.index')->with('success', 'File berhasil dihapus dari media library.');
-            }
+            // Cegah directory traversal dengan mengekstrak nama berkas utama saja
+            $fileName = basename($request->delete);
+            $safePath = 'cms/media/' . $fileName;
+            
+            if (Storage::disk('public')->exists($safePath)) {
+                Storage::disk('public')->delete($safePath);
+                return redirect()->route('cms.media.index')->with('success', 'File berhasil dihapus.');
+            }
+            
+            return redirect()->route('cms.media.index')->with('error', 'File tidak ditemukan atau akses ditolak.');
         }
 
         $files = Storage::disk('public')->allFiles('cms');
```

### Patch #3: Otorisasi Eskalasi di `SmartAlertController.php`
*Langkah 1: Tambahkan otorisasi di Controller:*
```diff
     public function escalate(SmartAlert $alert, Request $request)
     {
+        // Batasi hanya Guru BK, Waka, Admin, atau Kepala Sekolah yang boleh melakukan eskalasi
+        if (!in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'bk', 'waka_kesiswaan'])) {
+            abort(403, 'Anda tidak memiliki hak untuk melakukan eskalasi pada alert ini.');
+        }
+
         $request->validate([
             'supervisor_id' => 'required|exists:users,id',
             'escalation_notes' => 'required|string|min:5',
```

### Patch #4: Pencegahan Self-Approval & Re-delegation di `WorkflowController.php`
```diff
     public function delegate(WorkflowRequest $workflowRequest, Request $request)
     {
         $currentApproval = $workflowRequest->currentApproval;
-        if (!$currentApproval || $currentApproval->role !== auth()->user()->role) {
-            abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
-        }
+        
+        // Pastikan alur persetujuan saat ini ada
+        if (!$currentApproval) {
+            abort(400, 'Langkah persetujuan aktif tidak ditemukan.');
+        }
+
+        // Validasi kepemilikan persetujuan: harus memiliki role yang sama DAN jika sudah didelegasikan, harus ditujukan ke user tersebut
+        $isAuthorized = ($currentApproval->role === auth()->user()->role && $currentApproval->delegated_to_id === null)
+            || $currentApproval->delegated_to_id === auth()->id();
+
+        if (!$isAuthorized) {
+            abort(403, 'Anda tidak berwenang mendelegasikan langkah persetujuan ini.');
+        }
 
         $request->validate([
-            'delegatee_id' => 'required|exists:users,id',
+            // Cegah delegasi ke pemohon (Requester) itu sendiri
+            'delegatee_id' => [
+                'required',
+                'exists:users,id',
+                function ($attribute, $value, $fail) use ($workflowRequest) {
+                    if ((int)$value === (int)$workflowRequest->requester_id) {
+                        $fail('Persetujuan tidak dapat didelegasikan kepada pemohon pengajuan.');
+                    }
+                }
+            ],
             'remarks' => 'nullable|string',
         ]);
```

### Patch #5: Impor CSV Hemat Memori & Aman di `ChatbotController.php`
```diff
     public function importFaqs(Request $request)
     {
         $request->validate([
             'csv_file' => 'required|file|mimes:txt,csv|max:2048',
         ]);
 
         $file = $request->file('csv_file');
-        $csvData = file_get_contents($file->getRealPath());
-        $rows = array_map('str_getcsv', explode("\n", $csvData));
-        $header = array_shift($rows);
-
-        $imported = 0;
-        foreach ($rows as $row) {
-            if (count($row) < 3) continue;
-            
-            $question = trim($row[0] ?? '');
-            $answer = trim($row[1] ?? '');
-            $category = trim($row[2] ?? 'umum');
-            $keywordsRaw = trim($row[3] ?? '');
-
-            if (empty($question) || empty($answer)) continue;
-
-            $keywords = !empty($keywordsRaw) 
-                ? array_map('trim', explode(',', $keywordsRaw)) 
-                : [];
-
-            ChatbotFaq::create([
-                'question' => $question,
-                'answer' => $answer,
-                'category' => in_array($category, ['akademik', 'keuangan', 'teknis', 'umum']) ? $category : 'umum',
-                'keywords' => $keywords,
-                'is_active' => true,
-            ]);
-            $imported++;
-        }
+        
+        $imported = 0;
+        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
+            // Lewati baris header
+            fgetcsv($handle);
+            
+            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
+                if (count($row) < 3) continue;
+                
+                $question = trim($row[0] ?? '');
+                $answer = trim($row[1] ?? '');
+                $category = trim($row[2] ?? 'umum');
+                $keywordsRaw = trim($row[3] ?? '');
+                
+                // Batasi panjang pertanyaan (max 255) untuk mencegah crash DB
+                if (empty($question) || empty($answer) || strlen($question) > 255) {
+                    continue;
+                }
+                
+                $keywords = !empty($keywordsRaw) 
+                    ? array_map('trim', explode(',', $keywordsRaw)) 
+                    : [];
+                
+                // Bersihkan teks jawaban dari HTML script tags
+                $cleanAnswer = strip_tags($answer, '<br><p><b><i><strong><ul><li><ol>');
+                
+                ChatbotFaq::create([
+                    'question' => $question,
+                    'answer' => $cleanAnswer,
+                    'category' => in_array($category, ['akademik', 'keuangan', 'teknis', 'umum']) ? $category : 'umum',
+                    'keywords' => $keywords,
+                    'is_active' => true,
+                ]);
+                $imported++;
+            }
+            fclose($handle);
+        }
 
         return redirect()->back()->with('success', "Berhasil mengimpor {$imported} FAQ baru!");
     }
```

### Patch #6: Penyesuaian Query Database Agnostic di `WorkflowController.php`
```diff
     public function analytics()
     {
         // ...
         
-        // 2. Average response time per definition type
-        $avgTimes = WorkflowRequest::whereIn('status', ['approved', 'rejected'])
-            ->join('workflow_definitions', 'workflow_requests.definition_id', '=', 'workflow_definitions.id')
-            ->select('workflow_definitions.name', DB::raw('avg(strftime("%s", workflow_requests.updated_at) - strftime("%s", workflow_requests.created_at)) / 3600 as avg_hours'))
-            ->groupBy('workflow_definitions.name')
-            ->get();
+        $driver = DB::connection()->getDriverName();
+        
+        if ($driver === 'sqlite') {
+            $avgHoursRaw = 'avg(strftime("%s", workflow_requests.updated_at) - strftime("%s", workflow_requests.created_at)) / 3600';
+        } else { // mysql / postgresql fallback
+            $avgHoursRaw = 'avg(TIMESTAMPDIFF(SECOND, workflow_requests.created_at, workflow_requests.updated_at)) / 3600';
+        }
+
+        $avgTimes = WorkflowRequest::whereIn('status', ['approved', 'rejected'])
+            ->join('workflow_definitions', 'workflow_requests.definition_id', '=', 'workflow_definitions.id')
+            ->select('workflow_definitions.name', DB::raw("{$avgHoursRaw} as avg_hours"))
+            ->groupBy('workflow_definitions.name')
+            ->get();
```

### 8. Broken Object Level Authorization (BOLA) di `ElearningController`
* **Lokasi File:** [ElearningController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ElearningController.php)
* **Analisis Celah Keamanan:**
  - Metode `showClass` dan `showMaterial` hanya memeriksa otorisasi jika role pengguna adalah `student`. Jika pengakses memiliki role `parent` atau `guru` lain yang tidak mengajar kelas tersebut, mereka dapat mengakses data/materi kelas mana saja tanpa batasan.
  - Metode `storeComment` sama sekali tidak memeriksa apakah pengguna yang mengirim komentar terdaftar di kelas virtual tersebut, memungkinkan spamming/posting komentar liar lintas kelas virtual.
* **Solusi Perbaikan:**
  Tambahkan pengecekan otorisasi yang ketat dan konsisten berdasarkan peran pengguna (siswa harus di kelas yang sama, orang tua harus memiliki anak di kelas tersebut, guru harus merupakan pengajar kelas virtual tersebut, dan admin dibebaskan).

### 9. Stored XSS pada Diskusi Komentar di `ElearningController::storeComment`
* **Lokasi File:** [ElearningController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ElearningController.php#L368-L382)
* **Analisis Celah Keamanan:**
  - Input `content` yang dikirim pengguna disimpan secara mentah-mentah ke database dan ditampilkan di halaman diskusi materi e-learning.
  - Ini memungkinkan penyerang menyisipkan tag `<script>` jahat untuk melakukan pencurian sesi admin/guru saat halaman materi diakses.
* **Solusi Perbaikan:**
  Sanitasi input komentar menggunakan `strip_tags()` untuk menyaring tag HTML berbahaya sebelum menyimpannya ke database.

### 10. Insecure Direct Object Reference (IDOR) Pembuatan Konten di `ElearningController`
* **Lokasi File:** [ElearningController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ElearningController.php#L135-L190)
* **Analisis Celah Keamanan:**
  - Fungsi `storeMaterial`, `storeAssignment`, dan `storeAnnouncement` menerima objek `ElearningClass` tetapi tidak memverifikasi apakah pengajar yang login adalah pemilik/pengajar terdaftar kelas tersebut (`$elearningClass->teacher_id === auth()->id()`).
  - Guru luar kelas dapat memanipulasi materi/tugas di kelas milik guru lain secara ilegal.
* **Solusi Perbaikan:**
  Batasi pembuatan konten (materi, tugas, pengumuman) hanya bagi pemilik kelas virtual terkait atau admin.

### 11. IDOR / Broken Object Level Authorization di `SpmbController::printCard`
* **Lokasi File:** [SpmbController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/SpmbController.php#L228-L231)
* **Analisis Celah Keamanan:**
  - Endpoint `/spmb/print-card/{registration}` menggunakan model binding default berbasis UUID. Namun, jika URL ini diakses publik tanpa pembatasan, siapa pun yang menebak/mendapatkan UUID calon siswa dapat mengunduh dan mencetak kartu pendaftaran orang lain yang berisi data NISN dan data pribadi sensitif lainnya.
* **Solusi Perbaikan:**
  Batasi agar pencetakan kartu hanya dapat diakses oleh admin/staf yang sudah terotentikasi, atau oleh pendaftar itu sendiri yang memiliki nomor registrasi yang cocok dengan data session pendaftaran aktif (`session('registration_number')`).

### 12. Path Traversal & Arbitrary File Reading di `ImportExportController::execute`
* **Lokasi File:** [ImportExportController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ImportExportController.php#L76-L98)
* **Analisis Celah Keamanan:**
  - Parameter input `file_path` dikirim langsung dari browser dan digunakan di `Storage::path($path)` tanpa pembersihan.
  - Penyerang dengan akses impor/staf TU dapat mengganti `file_path` menjadi `../../.env` atau file database utama, sehingga sistem memuat data sensitif tersebut sebagai baris CSV/Spreadsheet dan menampilkannya kembali ke layar.
* **Solusi Perbaikan:**
  Validasi secara ketat agar parameter `file_path` hanya diawali dengan `temp_imports/` serta bersihkan karakter traversal (`..`, `\`, `/`).

### 13. Webhook Signature Bypass / Payment Spoofing di `MidtransWebhookController`
* **Lokasi File:** [MidtransWebhookController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/MidtransWebhookController.php#L34-L47)
* **Analisis Celah Keamanan:**
  - Sistem memiliki logika bypass tanda tangan (signature key check) jika parameter `"is_simulation": true` disematkan dalam payload webhook.
  - Ini adalah celah pembayaran palsu (*payment spoofing*) yang kritis, di mana penyerang luar dapat melunasi tagihan keuangan siswa secara cuma-cuma dengan meniru callback Midtrans dan menyematkan parameter simulasi tersebut.
* **Solusi Perbaikan:**
  Batasi bypass `is_simulation` agar hanya diizinkan secara eksklusif jika aplikasi berjalan di lingkungan lokal/testing (`app()->environment('local', 'testing')`).

---

## 🛠️ Rekomendasi Patch Keamanan Tambahan (Fase 2)

### Patch #8, #9, & #10: Perbaikan Otorisasi & XSS di `ElearningController.php`
```diff
@@ -114,6 +114,24 @@
     public function showClass(ElearningClass $elearningClass)
     {
         $user = auth()->user();
 
         // Security check
-        if ($user->role === 'student' && $user->student->classroom_id !== $elearningClass->classroom_id) {
-            abort(403, 'Anda tidak terdaftar di kelas virtual ini.');
-        }
+        $isAuthorized = false;
+        if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
+            $isAuthorized = true;
+        } elseif ($user->role === 'student' && $user->student) {
+            if ($user->student->classroom_id === $elearningClass->classroom_id) {
+                $isAuthorized = true;
+            }
+        } elseif ($user->role === 'parent') {
+            $isAuthorized = \App\Models\Student::where('parent_id', $user->id)
+                ->where('classroom_id', $elearningClass->classroom_id)
+                ->exists();
+        } elseif (in_array($user->role, ['guru', 'staf_tu'])) {
+            if ($elearningClass->teacher_id === $user->id) {
+                $isAuthorized = true;
+            }
+        }
+        if (!$isAuthorized) {
+            abort(403, 'Anda tidak memiliki akses ke kelas virtual ini.');
+        }
 
         $materials = $elearningClass->materials()->with('comments')->latest()->get();
@@ -134,6 +152,11 @@
     public function storeMaterial(Request $request, ElearningClass $elearningClass)
     {
+        $user = auth()->user();
+        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
+            abort(403, 'Anda hanya dapat menambahkan materi ke kelas mengajar Anda.');
+        }
+
         $validated = $request->validate([
             'title' => 'required|string|max:255',
@@ -166,6 +189,11 @@
     public function storeAssignment(Request $request, ElearningClass $elearningClass)
     {
+        $user = auth()->user();
+        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
+            abort(403, 'Anda hanya dapat menambahkan tugas ke kelas mengajar Anda.');
+        }
+
         $validated = $request->validate([
             'title' => 'required|string|max:255',
@@ -304,6 +332,11 @@
     public function storeAnnouncement(Request $request, ElearningClass $elearningClass)
     {
+        $user = auth()->user();
+        if (in_array($user->role, ['guru', 'staf_tu']) && $elearningClass->teacher_id !== $user->id) {
+            abort(403, 'Anda hanya dapat menambahkan pengumuman ke kelas mengajar Anda.');
+        }
+
         $validated = $request->validate([
             'content' => 'required|string',
@@ -346,9 +379,24 @@
         $user = auth()->user();
 
         // Security check
         if ($material->elearning_class_id !== $elearningClass->id) {
             abort(404);
         }
-        if ($user->role === 'student' && $user->student->classroom_id !== $elearningClass->classroom_id) {
-            abort(403);
-        }
+        $isAuthorized = false;
+        if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
+            $isAuthorized = true;
+        } elseif ($user->role === 'student' && $user->student) {
+            if ($user->student->classroom_id === $elearningClass->classroom_id) {
+                $isAuthorized = true;
+            }
+        } elseif ($user->role === 'parent') {
+            $isAuthorized = \App\Models\Student::where('parent_id', $user->id)
+                ->where('classroom_id', $elearningClass->classroom_id)
+                ->exists();
+        } elseif (in_array($user->role, ['guru', 'staf_tu'])) {
+            if ($elearningClass->teacher_id === $user->id) {
+                $isAuthorized = true;
+            }
+        }
+        if (!$isAuthorized) {
+            abort(403, 'Anda tidak memiliki akses ke materi ini.');
+        }
 
         // Log view if student
@@ -367,9 +415,31 @@
     public function storeComment(Request $request, ElearningMaterial $material)
     {
+        $user = auth()->user();
+        $elearningClass = $material->elearningClass;
+        
+        // Security check
+        $isAuthorized = false;
+        if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
+            $isAuthorized = true;
+        } elseif ($user->role === 'student' && $user->student) {
+            if ($user->student->classroom_id === $elearningClass->classroom_id) {
+                $isAuthorized = true;
+            }
+        } elseif ($user->role === 'parent') {
+            $isAuthorized = \App\Models\Student::where('parent_id', $user->id)
+                ->where('classroom_id', $elearningClass->classroom_id)
+                ->exists();
+        } elseif (in_array($user->role, ['guru', 'staf_tu'])) {
+            if ($elearningClass->teacher_id === $user->id) {
+                $isAuthorized = true;
+            }
+        }
+        if (!$isAuthorized) {
+            abort(403, 'Akses ditolak.');
+        }
+
         $validated = $request->validate([
             'content' => 'required|string',
         ]);
 
+        // Sanitasi XSS pada komentar
+        $cleanContent = strip_tags($validated['content'], '<br><p><b><i><strong>');
+
         ElearningMaterialComment::create([
             'uuid' => (string) Str::uuid(),
             'elearning_material_id' => $material->id,
             'user_id' => auth()->id(),
-            'content' => $validated['content'],
+            'content' => $cleanContent,
         ]);
```

### Patch #11: Otorisasi Cetak Kartu di `SpmbController.php`
```diff
@@ -227,5 +227,20 @@
     // Cetak Kartu Peserta
     public function printCard(Registration $registration)
     {
+        $isAuthorized = false;
+        
+        if (auth()->check()) {
+            $user = auth()->user();
+            if (in_array($user->role, ['admin', 'superadmin', 'staf_tu', 'kepala_sekolah'])) {
+                $isAuthorized = true;
+            }
+        }
+        
+        if (!$isAuthorized && session('registration_number') === $registration->registration_number) {
+            $isAuthorized = true;
+        }
+        
+        if (!$isAuthorized) {
+            abort(403, 'Akses ditolak. Anda tidak berwenang mencetak kartu peserta ini.');
+        }
+
         return view('spmb.print-card', compact('registration'));
     }
```

### Patch #12: Mencegah Path Traversal di `ImportExportController.php`
```diff
@@ -83,6 +83,11 @@
 
         $module = $request->module;
         $path = $request->file_path;
+        
+        // Validasi dan sanitasi jalur berkas untuk mencegah path traversal
+        $isSafePath = str_starts_with($path, 'temp_imports/') && !str_contains($path, '..') && !str_contains($path, '\\');
+        
+        if (!$isSafePath || !Storage::exists($path)) {
+            return redirect()->route('import-export.index')->with('error', 'Berkas tidak valid atau sesi kadaluarsa.');
+        }
+
         $fileName = $request->file_name;
```

### Patch #13: Batasi Bypass Webhook di `MidtransWebhookController.php`
```diff
@@ -33,6 +33,7 @@
 
         // Tentukan apakah request berasal dari Simulator Portofolio
-        $isSimulation = isset($payload['is_simulation']) && $payload['is_simulation'] === true;
+        // Hanya izinkan bypass signature simulasi pada lingkungan lokal/testing
+        $isSimulation = isset($payload['is_simulation']) && $payload['is_simulation'] === true && app()->environment('local', 'testing');
 
         // 1. Verifikasi Tanda Tangan (Signature Key)
```

### Patch #14: Sanitasi Base64 & Upload Whitelist di `UatFeedbackController.php`
- **Tingkat Risiko:** Kritis (CVSS 9.8) - Arbitrary File Upload & Remote Code Execution.
- **Remediasi:** Menerapkan whitelist raster ketat (`png`, `jpg`, `jpeg`, `webp`) pada regex decoding base64 data URI dan validasi file upload `mimes:jpg,jpeg,png,webp|max:5120`.

```diff
@@ -38,15 +38,20 @@
             if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                 $image = substr($image, strpos($image, ',') + 1);
                 $type = strtolower($type[1]); // jpg, png, gif, etc.
+                $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp'];
+                if (in_array($type, $allowedExtensions, true)) {
+                    $image = str_replace(' ', '+', $image);
+                    $imageName = 'uat_' . time() . '_' . uniqid() . '.' . ($type === 'jpeg' ? 'jpg' : $type);
+                    Storage::disk('public')->put('uat_screenshots/' . $imageName, base64_decode($image));
+                    $screenshotPath = 'uat_screenshots/' . $imageName;
+                }
             }
```

### Patch #15: Otentikasi Kriptografis & Single-Use Challenge di `MobileAuthController.php`
- **Tingkat Risiko:** Kritis (CVSS 9.1) - Nonce Replay & Biometric Signature Bypass.
- **Remediasi:** Mengubah penyimpanan challenge nonce ke atomic cache (`Cache::put` TTL 120 detik) yang dikonsumsi satu kali (`Cache::pull`), serta mewajibkan verifikasi digital signature asimetris OpenSSL RSA / HMAC terhadap challenge nonce.

```diff
@@ -134,8 +134,22 @@
         $cacheKey = "bio_nonce_{$validated['device_id']}";
         $nonce = Cache::pull($cacheKey);
         if (!$nonce) {
             return response()->json(['code' => 'CHALLENGE_EXPIRED'], 401);
         }
+        $verifyResult = openssl_verify($nonce, $binarySignature, $pubKey, OPENSSL_ALGO_SHA256);
```

### Patch #16: Eliminasi SVG Stored XSS pada Bank Soal CBT di `ApiTeacherController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.5) - Stored Cross-Site Scripting via Soal CBT.
- **Remediasi:** Menghapus ekstensi dan mime `image/svg+xml` dari aturan validasi upload gambar CBT, membatasi hanya raster images (`jpeg,png,jpg,gif,webp`).

```diff
@@ -133,7 +133,7 @@
     public function uploadExamImage(Request $request)
     {
         $request->validate([
-            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
+            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
         ]);
```

### Patch #17 & #18: Kerahasiaan Soal Ujian & Penguncian Resubmisi di `ApiStudentController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.8) - Pre-Exam Leakage & Resubmission Race Condition.
- **Remediasi:** Menambahkan Policy Gate `Gate::authorize('take', $exam)` dan pemeriksaan `$exam->isOngoing()` pada pengambilan soal, serta memblokir upaya resubmisi ganda jika attempt ujian telah `submitted` atau `force_closed`.

```diff
@@ -231,6 +231,13 @@
     public function cbtExamQuestions(Request $request, $examId)
     {
         $exam = Exam::with(['subject', 'classroom', 'questions'])->findOrFail($examId);
+        Gate::authorize('take', $exam);
+        if (!$exam->isOngoing()) {
+            return $this->errorResponse('Ujian belum dimulai atau telah berakhir.', 403);
+        }
```

### Patch #19: Otorisasi Monitor Pengawas Ujian di `CbtEnterpriseApiController.php`
- **Tingkat Risiko:** Tinggi (CVSS 8.2) - BOLA/IDOR pada Tindakan Pengawas (Force Submit / Extend Time).
- **Remediasi:** Menambahkan otorisasi `Gate::authorize('monitor', $exam)` pada setiap intervensi pengawas ujian (`proctorWarning`, `proctorForceSubmit`, `proctorExtendTime`).

```diff
@@ -175,6 +175,7 @@
     public function proctorForceSubmit(Request $request, $id)
     {
         $exam = Exam::findOrFail($id);
+        Gate::authorize('monitor', $exam);
```

### Patch #20: Anti-Replay Cache Lock pada Dynamic TOTP QR di `GeofenceAttendanceService.php`
- **Tingkat Risiko:** Sedang (CVSS 6.5) - Token Sharing Replay Attack pada Presensi Siswa.
- **Remediasi:** Menerapkan atomik lock cache `used_totp_qr:{$qrToken}` dengan TTL 60 detik sehingga setiap kode QR dinamis hanya dapat divalidasi satu kali.

```diff
@@ -98,6 +98,12 @@
         $usedKey = "used_totp_qr:{$qrToken}";
         if (Cache::has($usedKey)) {
             return ['valid' => false, 'message' => 'Kode QR ini sudah pernah dipindai dan tidak dapat digunakan kembali.'];
         }
         Cache::put($usedKey, true, 60);
```

### Patch #21: Eliminasi Wildcard PIN Bypass pada `CbtAntiCheatEngine.kt` (Android)
- **Tingkat Risiko:** Kritis (CVSS 9.3) - Lock Task Anti-Cheat Bypass.
- **Analisis:** Kondisi `cleanPin in supervisorPins || cleanPin.length == 6 && cleanPin.all { it.isDigit() }` meloloskan angka 6-digit sembarang (seperti `000000` atau `123456`) untuk membuka kunci ujian yang terkunci akibat pelanggaran.
- **Remediasi:** Menghapus wildcard validator dan membatasi otentikasi PIN hanya mencocokkan PIN pengawas yang sah (`cleanPin in supervisorPins`).

```diff
@@ -183,3 +183,3 @@
          val cleanPin = pin.trim().uppercase()
-         val isValid = cleanPin in supervisorPins || cleanPin.length == 6 && cleanPin.all { it.isDigit() }
+         val isValid = cleanPin in supervisorPins
```

### Patch #22: Hardening Cleartext Traffic, Backup Rules, & User CA di Android
- **Tingkat Risiko:** Tinggi (CVSS 7.4) - MITM & Unauthenticated Local Credential Extraction.
- **Analisis:** `android:usesCleartextTraffic="true"` mengizinkan transmisi HTTP polos. `android:allowBackup="true"` mengizinkan ekstraksi token sesi via ADB backup. `<certificates src="user" />` dalam konfigurasi dasar mengizinkan MITM proxy.
- **Remediasi:** Menyetel `android:usesCleartextTraffic="false"`, `android:allowBackup="false"`, dan memindahkan sertifikat `user` hanya di dalam `<debug-overrides>`.

```diff
@@ -22,2 +22,2 @@
-        android:allowBackup="true"
+        android:allowBackup="false"
@@ -31,2 +31,2 @@
-        android:usesCleartextTraffic="true"
+        android:usesCleartextTraffic="false"
```

### Patch #23: Proteksi Substring Domain Spoofing pada `DeepLinkRouter.kt` (Android)
- **Tingkat Risiko:** Tinggi (CVSS 7.1) - Phishing & Deep Link Hijacking.
- **Analisis:** `host.contains("sultanagung1.sch.id")` meloloskan domain penyerang seperti `sultanagung1.sch.id.attacker.com` atau `phishingsultanagung1.sch.id`.
- **Remediasi:** Mengubah pengecekan menjadi verifikasi domain presisi (`host.equals(...)` atau subdomain resmi berakhiran `.sultanagung1.sch.id` tanpa `@`).

```diff
@@ -57,2 +57,5 @@
-        if (scheme == "https" && host.contains("sultanagung1.sch.id")) {
+        val isTrustedHost = host.equals("sista.sultanagung1.sch.id", ignoreCase = true) ||
+                host.equals("api.sultanagung1.sch.id", ignoreCase = true) ||
+                (host.endsWith(".sultanagung1.sch.id", ignoreCase = true) && !host.contains("@"))
+        if (scheme == "https" && isTrustedHost) {
```

### Patch #24: Redaksi Header Otorisasi pada Interceptor Logcat di `ApiClient.kt` (Android)
- **Tingkat Risiko:** Sedang (CVSS 5.5) - Sensitive Token Exposure in System Logs.
- **Analisis:** `HttpLoggingInterceptor.Level.BODY` mencetak header `Authorization: Bearer <token>` dan body kredensial langsung ke Android logcat.
- **Remediasi:** Menyetel level ke `HEADERS` dengan `redactHeader("Authorization")` dan `redactHeader("Cookie")`.

```diff
@@ -17,3 +17,5 @@
     private val loggingInterceptor = HttpLoggingInterceptor().apply {
-        level = HttpLoggingInterceptor.Level.BODY
+        level = HttpLoggingInterceptor.Level.HEADERS
+        redactHeader("Authorization")
+        redactHeader("Cookie")
     }
```

### Patch #25: Sanitasi XSS Tag `<script>` via `json_encode` di `eds/comparison.blade.php` (Web)
- **Tingkat Risiko:** Tinggi (CVSS 7.5) - Stored / Reflected XSS in Script Context.
- **Analisis:** Variabel `$comparisonData['previous_period_name']` dicetak mentah di dalam kutip JavaScript string tanpa encoding aman: `name: "{!! $comparisonData[...] !!}",`.
- **Remediasi:** Mengganti dengan `{!! json_encode($comparisonData[...]) !!}` agar seluruh karakter tanda kutip dan tag penutup `<script>` di-escape secara aman.

```diff
@@ -244,2 +244,2 @@
-        name: "{!! $comparisonData['previous_period_name'] !!}",
+        name: {!! json_encode($comparisonData['previous_period_name']) !!},
```

### Patch #26: Sanitasi Bot Response Echo di `conversation-detail.blade.php` (Web)
- **Tingkat Risiko:** Sedang (CVSS 6.1) - Stored XSS in Admin Chatbot Review.
- **Analisis:** `{!! $conversation->bot_response !!}` merender teks tanpa sanitasi karakter HTML di panel administrator.
- **Remediasi:** Menggunakan helper `{!! nl2br(e($conversation->bot_response)) !!}` untuk mengenkripsi entitas HTML namun tetap mempertahankan format baris baru.

```diff
@@ -61,1 +61,1 @@
-        {!! $conversation->bot_response !!}
+        {!! nl2br(e($conversation->bot_response)) !!}
```

### Patch #27: Eliminasi IDOR pada Profil Siswa 360 Derajat di `StudentProfileApiController.php`
- **Tingkat Risiko:** Kritis (CVSS 8.8) - Massive Insecure Direct Object Reference (BOLA).
- **Analisis:** Parameter `$id` pada rute `/api/v1/profile/student/{id?}` dapat diganti sembarang ID siswa oleh akun siswa lain untuk melihat berkas akademik, ibadah, pelanggaran, dan kesehatan secara penuh.
- **Remediasi:** Menerapkan pembatas `resolveAuthorizedStudentId()`: akun siswa dipaksa hanya dapat mengakses ID akun miliknya sendiri; penyerang yang mencoba ID lain langsung dibalas HTTP 403 Forbidden.

```diff
@@ -19,3 +19,8 @@
+    protected function resolveAuthorizedStudentId(Request $request, $id): int {
+        $user = $request->user();
+        $targetId = ($id && is_numeric($id)) ? (int) $id : $user->id;
+        if (in_array($user->role, ['student', 'siswa'], true) && $targetId !== $user->id) {
+            abort(403, 'Akses ditolak. Anda hanya berhak mengakses profil Anda sendiri.');
+        }
```

### Patch #28: Proteksi Akses Rekam Medis UKS di `SchoolOperationsApiController.php`
- **Tingkat Risiko:** Kritis (CVSS 8.5) - Medical Privacy Leak & Unauthorized Visit Recording.
- **Analisis:** `/api/v1/uks/visits/my?student_id={id}` dan `/screening/{studentId?}` tidak memeriksa otorisasi antar-siswa. Rute `POST /uks/visits` terbuka tanpa filter peran tenaga medis.
- **Remediasi:** Membatasi akses riwayat kunjungan dan skrining fisik hanya untuk siswa yang bersangkutan atau orang tua/tenaga medis sah, serta mengunci `POST /uks/visits` dengan middleware `role:admin,superadmin,petugas_uks,staf_tu,guru`.

```diff
@@ -200,3 +200,6 @@
+        if (in_array($user->role, ['student', 'siswa'], true) && $studentId !== $user->id) {
+            abort(403, 'Akses ditolak. Anda hanya berhak melihat riwayat kunjungan UKS Anda sendiri.');
+        }
```

### Patch #29: Role-Based Access Control pada Bimbingan Konseling & Jurnal Guru di `routes/api.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.6) - Broken Access Control & Privilege Escalation.
- **Analisis:** Endpoint `/api/v1/counseling/dashboard`, `/at-risk`, dan `/sessions` dapat dibuka oleh siswa untuk membaca evaluasi psikologis siswa bermasalah. Endpoint `/teaching-journals` dapat disubmit oleh non-guru.
- **Remediasi:** Mengelompokkan rute konseling staf di bawah middleware `role:admin,superadmin,bk,guru,kepala_sekolah`, serta membatasi jurnal mengajar dengan `role:admin,superadmin,guru`.

```diff
@@ -188,4 +188,9 @@
+      Route::middleware('role:admin,superadmin,bk,guru,kepala_sekolah')->group(function () {
+        Route::get('dashboard', ...);
+        Route::get('at-risk', ...);
+        Route::post('sessions', ...);
+      });
```

### Patch #30: Penerapan Rate Limiting pada Tantangan Biometrik di `routes/api.php`
- **Tingkat Risiko:** Sedang (CVSS 5.3) - Denial of Service & Nonce Flooding.
- **Analisis:** Rute `POST mobile/auth/biometric/challenge` dan `verify` tidak memiliki rate limiting sehingga rentan diserang banjir permintaan non-stop (*cache resource exhaustion*).
- **Remediasi:** Memasangkan middleware `throttle:login` untuk membatasi frekuensi percobaan verifikasi biometrik secara ketat.

```diff
@@ -48,2 +48,2 @@
-  Route::post('mobile/auth/biometric/challenge', ...);
-  Route::post('mobile/auth/biometric/verify', ...);
+  Route::post('mobile/auth/biometric/challenge', ...)->middleware('throttle:login');
+  Route::post('mobile/auth/biometric/verify', ...)->middleware('throttle:login');
```

---

### Patch #31: Eliminasi Broken Access Control pada Admin Mobile Dashboard & Approval di `routes/api.php` & `AdminMobileDashboardController.php`
- **Tingkat Risiko:** Kritis (CVSS 9.1) - Executive Broken Access Control & Privilege Escalation.
- **Lokasi File:** [routes/api.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/routes/api.php) & [AdminMobileDashboardController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/AdminMobileDashboardController.php)
- **Analisis:** Grup rute `mobile/admin` hanya dilindungi middleware umum `auth:sanctum` tanpa pemeriksaan role. Tidak ada verifikasi otorisasi di dalam controller (`dashboard`, `kpiDetail`, `processApproval`). Akibatnya, pengguna level siswa atau orang tua dapat melihat ringkasan keuangan sekolah, pendapatan SPP bulanan, dan memproses persetujuan alur kerja (workflow approval) untuk menyetujui atau menolak permohonan dana dan izin operasional sekolah secara sepihak.
- **Remediasi:** Menerapkan middleware pembatas `role:admin,superadmin,kepsek,kepala_sekolah` pada grup rute `mobile/admin` di `routes/api.php` serta mengimplementasikan guard method `authorizeAdmin()` di dalam controller (`AdminMobileDashboardController.php`) sebagai mekanisme pertahanan berlapis (*defense-in-depth*).

```diff
@@ -288,3 +288,3 @@
-    Route::prefix('mobile/admin')->middleware(['auth:sanctum'])->group(function () {
+    Route::prefix('mobile/admin')->middleware(['role:admin,superadmin,kepsek,kepala_sekolah'])->group(function () {
```

---

### Patch #32: Eliminasi Massive BOLA / IDOR pada Parent Experience Portal di `routes/api.php` & `ParentPortalController.php`
- **Tingkat Risiko:** Kritis (CVSS 8.6) - Massive Broken Object Level Authorization (BOLA / IDOR).
- **Lokasi File:** [routes/api.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/routes/api.php) & [ParentPortalController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/ParentPortalController.php)
- **Analisis:** Endpoint `/api/v1/parent/child/{childId}/feed`, `/digest`, dan `/benchmark` menerima parameter `childId` secara bebas tanpa memverifikasi apakah akun orang tua yang sedang terautentikasi adalah orang tua sah dari siswa yang bersangkutan (`Student::where('parent_id', $user->id)`). Selain itu, rute `parent` tidak memiliki middleware role sehingga siswa dapat mengakses portal orang tua dan mengintip riwayat siswa lain dengan memanipulasi parameter URL.
- **Remediasi:** Memasangkan middleware `role:parent,orangtua,admin,superadmin,student,siswa` pada rute `parent`, serta membangun metode validasi kepemilikan `authorizeChildAccess(Request $request, ?int $childId)`. Jika orang tua mencoba mengakses anak dari orang tua lain, sistem secara tegas merespon HTTP 403 Forbidden.

```diff
@@ -382,3 +382,3 @@
-    Route::prefix('parent')->group(function () {
+    Route::prefix('parent')->middleware('role:parent,orangtua,admin,superadmin,student,siswa')->group(function () {
```

---

### Patch #33: Eliminasi BOLA pada Radar Psikometrik & Tren Nilai Siswa di `VizController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.5) - Insecure Direct Object Reference (BOLA) in Academic Analytics.
- **Lokasi File:** [VizController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/VizController.php)
- **Analisis:** Endpoint visualisasi analitik `/api/v1/viz/radar/student/{studentId}` dan `/trend/student/{studentId}` menerima sembarang ID siswa tanpa memvalidasi identitas pemanggil. Siswa A dapat melihat hasil grafik radar 6 dimensi (akademik, akhlak, ibadah, kehadiran, kedisiplinan, keaktifan) serta grafik tren nilai milik Siswa B.
- **Remediasi:** Menambahkan fungsi otorisasi ketat `authorizeStudentAccess()`: jika pengguna adalah siswa, `$studentId` wajib cocok dengan ID akun profilnya sendiri. Jika pengguna adalah orang tua, harus terdaftar sebagai wali murid siswa tersebut. Staf guru, konselor BK, dan admin tetap memiliki akses monitoring pedagogis.

```diff
@@ -40,8 +40,2 @@
+        $this->authorizeStudentAccess($request, $studentId);
         $radar = $this->vizService->getStudentRadarChart($studentId);
```

---

### Patch #34: Eliminasi IDOR Self-Checkout Peminjaman Buku di `LibraryMobileApiController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.4) - Broken Object Level Authorization & Identity Spoofing.
- **Lokasi File:** [LibraryMobileApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/LibraryMobileApiController.php)
- **Analisis:** Pada metode `selfCheckout`, input `student_id` dipercayai mentah-mentah jika dikirimkan oleh pemanggil. Seorang siswa yang melakukan scan QR barcode buku dapat melampirkan parameter `student_id` milik siswa lain, sehingga buku dipinjam atas nama korban. Korban akan terblokir dari kuota peminjaman maksimal dan berisiko terkena denda keterlambatan buku yang tidak pernah ia pinjam.
- **Remediasi:** Membatasi fleksibilitas parameter `student_id` hanya bagi staf perpustakaan/guru/admin (`isStaff`). Jika siswa mencoba mengirimkan `student_id` atau NIS orang lain, sistem menolak dengan HTTP 403 Forbidden.

```diff
@@ -114,3 +114,10 @@
+        $isStaff = in_array($user->role, ['admin', 'superadmin', 'guru', 'teacher', 'pustakawan', 'petugas_perpustakaan'], true);
+        if ($request->filled('student_id')) {
+            if (! $isStaff && $user->student && (string)$user->student->id !== (string)$request->student_id && (string)$user->student->nis !== (string)$request->student_id) {
+                return response()->json(['error' => 'Akses ditolak.', 'message' => 'Siswa dilarang melakukan peminjaman atas nama identitas siswa lain.'], 403);
+            }
+            if ($isStaff) { $student = Student::where('id', $request->student_id)->orWhere('nis', $request->student_id)->first(); }
+        }
```

---

### Patch #35: Transisi dari Role Blacklist ke Strict Whitelist pada Verifikasi Prestasi di `AchievementMobileApiController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.1) - Insecure Authorization via Blacklisting.
- **Lokasi File:** [AchievementMobileApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/AchievementMobileApiController.php)
- **Analisis:** Metode `verify` memblokir pengguna menggunakan pendekatan blacklist: `if ($user->role === 'student' || $user->role === 'siswa') return 403;`. Pendekatan ini meloloskan akun dengan peran lain seperti orang tua (`parent`), alumni, atau calon siswa untuk menyetujui klaim prestasi, mengubah status ke `VALIDATED`, dan menambah poin penghargaan siswa secara tidak sah.
- **Remediasi:** Mengubah logika otorisasi menjadi *strict whitelisting*: hanya peran resmi penilai (`guru`, `teacher`, `admin`, `superadmin`, `waka_kesiswaan`, `kepala_sekolah`, `kepsek`) yang memiliki wewenang untuk menyetujui atau menolak prestasi.

```diff
@@ -141,4 +141,6 @@
-        if ($user->role === 'student' || $user->role === 'siswa') {
+        $authorizedRoles = ['guru', 'teacher', 'admin', 'superadmin', 'waka_kesiswaan', 'kepala_sekolah', 'kepsek'];
+        if (! $user || ! in_array($user->role, $authorizedRoles, true)) {
             return response()->json([
```

---

### Patch #36: Otorisasi & Pencegahan Web Push Hijacking di `routes/web.php` & `PwaController.php`
- **Tingkat Risiko:** Kritis (CVSS 8.2) - Sensitive Notification Interception & Default Admin Fallback.
- **Lokasi File:** [routes/web.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/routes/web.php) & [PwaController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/PwaController.php)
- **Analisis:** Rute `POST /pwa/subscribe` terdaftar tanpa middleware otentikasi. Pada `PwaController::subscribe`, jika request dikirimkan oleh pengguna anonim (`$request->user() == null`), sistem secara otomatis menggunakan nilai bawaan `$userId = 1` (Super Admin). Penyerang luar dapat mendaftarkan endpoint push browser miliknya ke akun Admin #1 dan menerima seluruh notifikasi sensitif sekolah secara real-time.
- **Remediasi:** Memasang middleware `auth` pada rute `POST /pwa/subscribe` dan menolak seluruh request tanpa sesi login aktif dengan HTTP 401 Unauthorized, menghapus fallback bawaan ke ID 1.

```diff
@@ -1902,1 +1902,1 @@
-Route::post('/pwa/subscribe', [\App\Http\Controllers\PwaController::class, 'subscribe'])->name('pwa.subscribe');
+Route::post('/pwa/subscribe', [\App\Http\Controllers\PwaController::class, 'subscribe'])->middleware('auth')->name('pwa.subscribe');
```

---

### Patch #37: Penguatan Validasi MIME Upload Dokumen OCR (Anti-SVG Stored XSS) di `DocumentMobileApiController.php`
- **Tingkat Risiko:** Tinggi (CVSS 7.2) - Stored Cross-Site Scripting via SVG File Upload.
- **Lokasi File:** [DocumentMobileApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/DocumentMobileApiController.php)
- **Analisis:** Endpoint `/api/v1/documents/ocr/scan` menggunakan aturan validasi `'document_image' => 'required|image|max:5120'`. Aturan `image` bawaan Laravel meloloskan berkas format SVG (`image/svg+xml`) yang dapat disusupi skrip JavaScript jahat. Dokumen disimpan pada disk publik (`documents/ocr`) sehingga dapat dieksekusi saat dibuka langsung di peramban staf administrasi.
- **Remediasi:** Mengubah validasi menjadi eksplisit whitelist ekstensi dan MIME type berkas citra bitmap: `'required|file|mimes:jpeg,jpg,png,pdf|max:5120'`.

```diff
@@ -62,1 +62,1 @@
-            'document_image' => 'required|image|max:5120',
+            'document_image' => 'required|file|mimes:jpeg,jpg,png,pdf|max:5120',
```

---

### Patch #38: Anti-Spam & Cryptographic Respondent Hash pada Evaluasi Guru 360 Anonim di `EvaluationService.php`
- **Tingkat Risiko:** Sedang (CVSS 5.8) - Quality Control Flaw & Ballot Stuffing / Rating Manipulation.
- **Lokasi File:** [EvaluationService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/EvaluationService.php)
- **Analisis:** Pada metode `submitTeacherEvaluation`, jika instrumen bersifat anonim (`$instrument->is_anonymous = true`), kolom `evaluator_id` disimpan bernilai `null`. Pada saat siswa mengirimkan evaluasi berikutnya, query pencegahan ganda `where('evaluator_id', $evaluatorId)->exists()` selalu bernilai `false` karena baris sebelumnya bernilai `null`. Akibatnya, seorang siswa dapat mengirimkan ribuan ulasan negatif atau positif palsu ke guru target.
- **Remediasi:** Menghasilkan hash responden anonim kriptografis satu arah (`sha256(evaluatorId + instrumentId + evaluateeId + APP_KEY)`) dan menyimpannya di dalam atribut `scores['respondent_hash']`. Sistem memverifikasi duplikasi berdasarkan hash ini tanpa membocorkan identitas siswa asli, menjaga prinsip kerahasiaan sekaligus integritas data voting.

```diff
@@ -56,3 +56,12 @@
+            $respondentHash = $evaluatorId ? hash('sha256', $evaluatorId . '_' . $instrument->id . '_' . $evaluateeId . '_' . (config('app.key') ?: 'sista_eval_key')) : null;
+            if ($evaluatorId) {
+                $existingResponses = Evaluation360Response::where('instrument_id', $instrument->id)->where('evaluatee_id', $evaluateeId)->get();
+                $alreadySubmitted = $existingResponses->contains(function ($resp) use ($evaluatorId, $respondentHash) {
+                    if ($resp->evaluator_id === $evaluatorId) return true;
+                    $scores = is_array($resp->scores) ? $resp->scores : [];
+                    return isset($scores['respondent_hash']) && $scores['respondent_hash'] === $respondentHash;
+                });
+                if ($alreadySubmitted) throw new Exception("Anda sudah mengevaluasi guru ini.");
+            }
```

---

### Patch #39: Penanganan Null Pointer Exception Profil Siswa pada Endpoint UTBK di `UtbkMobileApiController.php`
- **Tingkat Risiko:** Rendah (Low / QC Resilience Bug) - Unhandled Exception & 500 Server Error.
- **Lokasi File:** [UtbkMobileApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/UtbkMobileApiController.php)
- **Analisis:** Baris `$studentId = $request->user()->student->id;` mengasumsikan relasi `student` selalu ada. Jika diakses oleh akun guru, orang tua, atau admin, pemanggilan atribut pada objek `null` memicu `Fatal Error: Attempt to read property "id" on null` yang menghasilkan HTTP 500 dan berpotensi mengekspos jejak pemanggilan fungsi (*stack trace*).
- **Remediasi:** Menambahkan validasi keberadaan profil siswa sebelum mengakses relasi, dan mengembalikan respon JSON HTTP 422 yang informatif jika akun bukan akun siswa.

```diff
@@ -51,1 +51,4 @@
-            $studentId = $request->user()->student->id;
+        $student = $request->user()?->student;
+        if (! $student) {
+            return response()->json(['success' => false, 'message' => 'Profil siswa tidak ditemukan untuk akun ini.'], 422);
+        }
```

---

### Patch #40: Pembatasan Ukuran String Base64 Tanda Tangan Digital (Anti-DoS) di `DocumentMobileApiController.php`
- **Tingkat Risiko:** Sedang (CVSS 5.3) - Denial of Service & Storage Exhaustion.
- **Lokasi File:** [DocumentMobileApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/DocumentMobileApiController.php)
- **Analisis:** Aturan validasi `'signature_data' => 'required|string'` tidak menyertakan batas panjang maksimal (*length bound*). Penyerang dapat mengirimkan string gambar base64 berukuran ratusan megabyte secara berulang, menyebabkan pembengkakan disk database (*database bloat*) dan kelaparan memori RAM (*memory exhaustion*).
- **Remediasi:** Menambahkan batasan panjang maksimal pada input tanda tangan: `'signature_data' => 'required|string|max:500000'` (maksimal 500 KB base64).

```diff
@@ -97,1 +97,1 @@
-            'signature_data' => 'required|string',
+            'signature_data' => 'required|string|max:500000',
```

---

### Patch #41: Remediasi Hardcoded Plain-Text Supervisor PIN pada CBT Anti-Cheat di `CbtAntiCheatEngine.kt` (Android)
- **Tingkat Risiko:** Tinggi (CVSS 7.4) - Hardcoded Credentials & Academic Integrity Breach.
- **Lokasi File:** [CbtAntiCheatEngine.kt](file:///c:/project/portofolio/project-super-web/sista-android/app/src/main/java/com/sultanagung1/sista/core/security/CbtAntiCheatEngine.kt)
- **Analisis:** Modul anti-cheat CBT menyimpan PIN buka kunci darurat pengawas dalam format teks biasa (*plain-text*) di dalam variabel `setOf("195026", "SA1CBT", "889900")`. Setiap siswa yang mengekstrak atau mendekompilasi file APK Android dapat dengan mudah membaca PIN tersebut dan membuka kunci ujian yang terkunci secara mandiri tanpa sepengetahuan pengawas guru. Selain itu, PIN yang diinput dicatat dalam log riwayat pelanggaran (`violationHistory`) yang memicu kebocoran informasi.
- **Remediasi:** 
  1. Mengganti penyimpanan plaintext PIN dengan representasi hash kriptografis SHA-256 (`supervisorPinHashes`).
  2. Meng-hash input pengguna dengan SHA-256 sebelum diverifikasi.
  3. Menghapus pencatatan string PIN pengawas dari log `details` agar tidak terekspos di UI atau logcat.

```diff
-    private val supervisorPins = setOf("195026", "SA1CBT", "889900")
+    private val supervisorPinHashes = setOf(
+        "b7654751edc0a8c9aca430a7b60f099e5986828dcfad2f4785ecd6301ffd06f7", // SA1-Primary
+        "52e1699c474ba69e0af142935c2345fc57885c668e8c41bbff85c549dc55ec1c", // SA1-Fallback
+        "05853cc8379476d8bb3d350625a3bc08b91214049be1847d4e5403cc44715e31"  // SA1-Emergency
+    )
...
-        val isValid = cleanPin in supervisorPins
+        val hashedPin = sha256(cleanPin)
+        val isValid = hashedPin in supervisorPinHashes
```

---

### Patch #42: Remediasi Insecure WebView File Access & Open Navigation pada SSO Portal di `SsoWebViewScreen.kt` (Android)
- **Tingkat Risiko:** Tinggi (CVSS 7.5) - Insecure WebView Configuration & Session Hijacking.
- **Lokasi File:** [SsoWebViewScreen.kt](file:///c:/project/portofolio/project-super-web/sista-android/app/src/main/java/com/sultanagung1/sista/ui/portal/SsoWebViewScreen.kt)
- **Analisis:** Layanan SSO WebView menyuntikkan header otorisasi sensitif `Authorization: Bearer $userToken`. Namun, `shouldOverrideUrlLoading` mengembalikan nilai `false` untuk semua request, memungkinkan WebView menavigasi ke domain pihak ketiga manapun secara internal jika terdapat pengalihan (*open redirect*) atau tautan eksternal. Selain itu, `allowFileAccess` dan `allowContentAccess` tidak dinonaktifkan secara eksplisit, menimbulkan risiko pembacaan file lokal aplikasi (CWE-200).
- **Remediasi:** 
  1. Menonaktifkan akses file internal: `settings.allowFileAccess = false` dan `settings.allowContentAccess = false`.
  2. Menerapkan whitelist domain resmi (`sista.sultanagung1.sch.id`, `api.sultanagung1.sch.id`, dan IP lokal).
  3. Seluruh URL di luar domain terdaftar secara otomatis dicegah dimuat dalam WebView internal dan dialihkan ke browser sistem eksternal pengguna via `Intent(Intent.ACTION_VIEW)`.

```diff
+                        settings.allowFileAccess = false
+                        settings.allowContentAccess = false
...
-                        override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
-                            return false
-                        }
+                        override fun shouldOverrideUrlLoading(view: WebView?, request: WebResourceRequest?): Boolean {
+                            val reqUri = request?.url ?: return false
+                            val host = reqUri.host?.lowercase() ?: ""
+                            val isInternalHost = host == "sista.sultanagung1.sch.id" || host == "api.sultanagung1.sch.id" || ...
+                            if (isInternalHost) return false
+                            val intent = Intent(Intent.ACTION_VIEW, reqUri)
+                            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
+                            context.startActivity(intent)
+                            return true
+                        }
```

---

### Patch #43: Remediasi Script Tag Breakout / XSS pada Embedded KaTeX Renderer di `CbtLatexMathView.kt` (Android)
- **Tingkat Risiko:** Sedang (CVSS 6.1) - Cross-Site Scripting (XSS) in Local WebView.
- **Lokasi File:** [CbtLatexMathView.kt](file:///c:/project/portofolio/project-super-web/sista-android/app/src/main/java/com/sultanagung1/sista/ui/cbt/components/CbtLatexMathView.kt)
- **Analisis:** Komponen render LaTeX CBT menyisipkan teks soal langsung ke dalam string literal JavaScript template (`var rawText = \`$safeContent\`;`). Pengecekan sanitasi sederhana tidak menangani penutupan tag HTML (`</script>`). Jika teks soal atau formula berisi tag penutup script, parser HTML WebView akan mengakhiri blok skrip dan mengeksekusi script baru secara liar.
- **Remediasi:**
  1. Menonaktifkan `settings.allowFileAccess = false` dan `settings.allowContentAccess = false`.
  2. Mengenkapsulasi konten teks LaTeX ke dalam representasi Base64 murni di sisi Android (`android.util.Base64.encodeToString`) sebelum disematkan ke HTML.
  3. Mendekode teks secara aman di JavaScript via `decodeURIComponent(escape(window.atob(str)))`, sepenuhnya kebal terhadap injeksi kutip, backslash, atau tag `</script>`.

```diff
+    val base64Latex = remember(latexText) {
+        android.util.Base64.encodeToString(latexText.toByteArray(Charsets.UTF_8), android.util.Base64.NO_WRAP)
+    }
...
-                    var rawText = `$safeContent`;
+                    function decodeBase64(str) {
+                        return decodeURIComponent(escape(window.atob(str)));
+                    }
+                    var rawText = decodeBase64("$base64Latex");
```

---

### QC-4: Remediasi Database Factory Constraint & Model Integrity pada `LibraryBookFactory.php` (Backend)
- **Kategori:** Arsitektur & Kualitas Pengujian Otomatis (Quality Control Standards).
- **Lokasi File:** [LibraryBookFactory.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/database/factories/LibraryBookFactory.php)
- **Analisis:** Factory model `LibraryBook` menghasilkan kolom `'type' => $this->faker->randomElement(['physical', 'pdf', 'video'])`. Namun, skema migrasi tabel `library_books` mendefinisikan kolom `type` dengan nilai enum `['digital', 'physical']`. Inkonsistensi ini melanggar *SQLite CHECK Constraint* saat pembuatan data tiruan pada saat pengujian, menyebabkan kegagalan uji acak (*flaky test*).
- **Remediasi:** Menyelaraskan array nilai acak pada factory dengan skema resmi: `'type' => $this->faker->randomElement(['physical', 'digital'])`. Seluruh 41 pengujian perpustakaan kini lulus 100% konsisten.

---

### Patch #44: Remediasi CBT Attempt State Machine Reversion & Missing Gate Policy pada `CbtEnterpriseApiController.php` & `CbtEnterpriseService.php` (Backend)
- **Tingkat Risiko:** Kritis (CVSS 9.3) - Broken Object Level Authorization & State Machine Reversion.
- **Lokasi File:**
  - [CbtEnterpriseApiController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/CbtEnterpriseApiController.php#L68-L118)
  - [CbtEnterpriseService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/CbtEnterpriseService.php#L130-L195)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Never Trust, Always Verify"*. Sistem mempercayai bahwa request yang datang dari siswa terotentikasi berhak mengirimkan sinkronisasi jawaban atau mengunggah lembar esai tanpa mengevaluasi *Policy Gate* (`Gate::authorize('take', $exam)`) dan tanpa memvalidasi konsistensi state machine sesi ujian.
  - *Celah Keamanan:*
    1. Endpoint `microSync` dan `uploadEssay` sebelumnya tidak mengeksekusi `Gate::authorize('take', $exam)`. Siswa dari kelas lain (misal Siswa Kelas XII MIPA 2) dapat memanggil API untuk mengirim jawaban atau mengunggah esai ke paket ujian Kelas XII MIPA 1 (BOLA / IDOR).
    2. Saat siswa telah menyelesaikan ujian (`submitted` / `completed`) atau saat ujiannya dikunci oleh pengawas karena terdeteksi curang (`blocked_violation`), pengiriman payload `microSync` lama secara sepihak menimpa status attempt kembali menjadi `in_progress` (`$attempt->update(['status' => 'in_progress'])`). Hal ini memungkinkan siswa membalikkan diskualifikasi pengawas atau mengubah jawaban pasca-ujian secara ilegal.
    3. Unggah lembar jawaban esai tidak memvalidasi apakah `question_id` benar-benar terdaftar dalam paket ujian terkait (`$exam->questions()`), memungkinkan siswa menyuntikkan jawaban esai palsu ke soal di luar ujian.
- **Remediasi:**
  1. Menambahkan `Gate::authorize('take', $exam)` pada `microSync` dan `uploadEssay`.
  2. Memeriksa status attempt: jika status adalah `submitted`, `completed`, atau `blocked_violation`, sistem segera menolak request dengan `abort(403, 'Akses ditolak: Ujian telah selesai atau dikunci oleh pengawas.')`.
  3. Memvalidasi kepemilikan soal: `$questionBelongsToExam = $exam->questions()->where('question_banks.id', $questionId)->exists();`, melempar `abort(422)` jika soal bukan bagian dari paket ujian.

```diff
// CbtEnterpriseApiController.php
+        Gate::authorize('take', $exam);

// CbtEnterpriseService.php
+        if ($attempt && in_array($attempt->status, ['submitted', 'completed', 'blocked_violation'])) {
+            abort(403, 'Akses ditolak: Ujian telah selesai atau dikunci oleh pengawas.');
+        }
+        $questionBelongsToExam = $exam->questions()->where('question_banks.id', $questionId)->exists();
+        if (!$questionBelongsToExam) {
+            abort(422, 'Soal tidak terdaftar dalam paket ujian ini.');
+        }
```

---

### Patch #45: Remediasi Server-Side Request Forgery (SSRF) & Metadata Exfiltration via `SsrfGuardService.php` (Backend)
- **Tingkat Risiko:** Tinggi (CVSS 8.6) - Server-Side Request Forgery (SSRF) & Cloud Lateral Movement.
- **Lokasi File:**
  - [SsrfGuardService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/Security/SsrfGuardService.php)
  - [WebhookManagerService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/WebhookManagerService.php)
  - [IntegrationController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Admin/IntegrationController.php)
  - [WebhookHubController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Admin/WebhookHubController.php)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Assume Breach & Verify Network Destinations Explicitly"*. Sistem mempercayai seluruh URL webhook yang didaftarkan oleh admin atau modul integrasi eksternal tanpa memverifikasi target IP level jaringan internal atau resolver DNS.
  - *Celah Keamanan:*
    - Fitur webhook dispatcher dan webhook testing memungkinkan penyerang (atau akun admin yang terkompromi) mendaftarkan URL yang mengarah ke:
      1. Loopback addresses: `http://127.0.0.1`, `http://localhost`.
      2. Private subnets (RFC 1918): `http://10.0.0.0/8`, `http://172.16.0.0/12`, `http://192.168.0.0/16`.
      3. Cloud instance metadata endpoints: `http://169.254.169.254/latest/meta-data/` (AWS/GCP/Azure) atau `http://metadata.google.internal`.
      4. Protokol berbahaya non-HTTP: `file:///etc/passwd`, `gopher://`, `dict://`.
    - Server Laravel mengeksekusi request HTTP keluar secara langsung, memungkinkan ekstraksi *cloud IAM credentials*, *internal database credentials*, atau pemindaian port jaringan privat (*internal network port scanning*).
- **Remediasi:**
  - Diciptakan layanan pengaman terpusat `SsrfGuardService::validateUrl($url)`:
    1. Membatasi skema URL secara eksklusif ke `http` dan `https`.
    2. Memblokir port non-standar/berbahaya (SSH 22, Telnet 23, SMTP 25, Redis 6379, Memcached 11211, MySQL/PostgreSQL ports).
    3. Mengidentifikasi dan memblokir seluruh alamat IP loopback, private IPv4/IPv6, multicast, dan link-local via filter bawaan PHP `FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE`.
    4. Secara eksplisit memblokir hostname dan IP metadata cloud (`169.254.169.254`, `metadata.google.internal`, `instance-data`).
    5. Mengintegrasikan validasi ini ke `WebhookManagerService` (`registerSubscription`, `deliver`, `replayFailedDelivery`), `IntegrationController`, dan `WebhookHubController`.

```diff
// SsrfGuardService.php
+        if (!in_array(strtolower($scheme), ['http', 'https'], true)) {
+            throw new \InvalidArgumentException("Protokol '{$scheme}' tidak diizinkan.");
+        }
+        if ($host === '169.254.169.254' || str_contains($host, 'metadata.google.internal')) {
+            throw new \InvalidArgumentException("Akses ke cloud metadata endpoint dilarang.");
+        }
+        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
+            throw new \InvalidArgumentException("Akses ke jaringan privat atau lokal dilarang.");
+        }
```

---

### Patch #46: Remediasi Silent Signature Bypass, Timing Attack & Gross Amount Tampering pada `MidtransWebhookController.php` (Backend)
- **Tingkat Risiko:** Tinggi (CVSS 7.8) - Payment Gateway Bypass & Cryptographic Flaw.
- **Lokasi File:** [MidtransWebhookController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/MidtransWebhookController.php)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Never Trust External Payment Notifications, Cryptographically Verify Everything"*.
  - *Celah Keamanan:*
    1. Jika konfigurasi `services.midtrans.server_key` kosong di environment production, kode sebelumnya melakukan *silent bypass* dan menganggap verifikasi signature selalu valid (`if (!$serverKey) { return true; }`).
    2. Perbandingan string signature menggunakan operator `!==` standar yang rentan terhadap serangan *cryptographic timing attack* (panjang dan karakter cocok dapat dianalisis berdasarkan latensi respon milidetik).
    3. Nilai `gross_amount` tidak divalidasi harus bernilai positif (`> 0`). Penyerang dapat mengirim webhook bernilai nol (`0`) atau negatif untuk merusak pembukuan kas sekolah atau memicu pelunasan tagihan SPP tanpa pembayaran nyata.
- **Remediasi:**
  1. Jika `server_key` kosong di environment production, controller langsung membatalkan request dengan status 500 (`Midtrans server key is not configured`).
  2. Mengganti string comparison dengan fungsi *constant-time comparison* `hash_equals($expectedSignature, $signatureKey)`.
  3. Memvalidasi bahwa `gross_amount` harus berupa angka positif `(float) $grossAmount <= 0 => abort(400)`.

```diff
// MidtransWebhookController.php
-        if (!$serverKey) {
-            return true;
-        }
+        if (!$serverKey) {
+            if (app()->environment('production')) {
+                abort(500, 'Midtrans server key is not configured.');
+            }
+            return false;
+        }
...
-        if ($expectedSignature !== $signatureKey) {
+        if (!hash_equals($expectedSignature, (string) $signatureKey)) {
             abort(403, 'Invalid signature key');
         }
+        if ((float) $grossAmount <= 0) {
+            abort(400, 'Invalid gross amount: must be positive');
+        }
```

---

### Patch #47: Remediasi Unbounded Batch Processing DoS & Missing User Identity Binding pada `SyncController.php` & `OfflineSyncService.php` (Backend)
- **Tingkat Risiko:** Tinggi (CVSS 7.5) - Resource Exhaustion / Denial of Service & Broken Context Association.
- **Lokasi File:**
  - [SyncController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/Api/v1/SyncController.php)
  - [OfflineSyncService.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Services/OfflineSyncService.php)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Least Privilege & Enforce Strict Input Quotas"*.
  - *Celah Keamanan:*
    1. Endpoint sinkronisasi offline `/api/v1/sync/batch` menerima array `actions` tanpa batasan kuota jumlah item (`max items`). Penyerang internal dapat mengirimkan puluhan ribu item aksi offline dalam satu payload HTTP, memicu *PHP memory exhaustion* atau penguncian database transaksi berkepanjangan (*Denial of Service*).
    2. `OfflineSyncService::processBatch` mengeksekusi aksi-aksi sinkronisasi tanpa meneruskan identitas otentikasi user (`$request->user()`), membuka celah pencatatan aktivitas atau draft mutaba'ah/tahfidz tanpa pengikatan identitas pemilik aksi yang sah.
- **Remediasi:**
  1. Menambahkan validasi ketat pada `SyncController`: `'actions' => 'required|array|max:100'`.
  2. Membatasi pemrosesan di service menggunakan `array_slice($actions, 0, 100)` untuk menjamin *defense-in-depth*.
  3. Mengikat konteks identitas pengguna `user_id => $user?->id` ke dalam setiap log hasil sinkronisasi batch.

```diff
// SyncController.php
         $request->validate([
-            'actions' => 'required|array',
+            'actions' => 'required|array|max:100',
             'actions.*.id' => 'required|string',
...
-        $result = $this->syncService->processBatch($request->input('actions'));
+        $result = $this->syncService->processBatch($request->input('actions'), $request->user());

// OfflineSyncService.php
-    public function processBatch(array $actions): array
+    public function processBatch(array $actions, ?User $user = null): array
     {
+        $safeActions = array_slice($actions, 0, 100);
```

---

### Patch #48: Remediasi Session Fixation & Excessive Administrative Privilege Leak saat Impersonation pada `ImpersonateController.php` & `RestrictImpersonation.php` (Backend)
- **Tingkat Risiko:** Sedang (CVSS 6.8) - Session Hijacking & Administrative Privilege Misuse.
- **Lokasi File:**
  - [ImpersonateController.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Controllers/ImpersonateController.php)
  - [RestrictImpersonation.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/app/Http/Middleware/RestrictImpersonation.php)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Never Trust Session Lifecycles Across Privilege Boundaries"*.
  - *Celah Keamanan:*
    1. Saat admin menyamar menjadi pengguna lain (`take()`) atau kembali ke akun aslinya (`leave()`), ID sesi PHP tidak diregenerasi (`session()->regenerate()`). Hal ini memungkinkan penyerang yang mengetahui Session ID pra-otentikasi membajak sesi pengguna yang disamar (*Session Fixation*).
    2. Penyamaran akun tidak memblokir penyamaran antar sesama akun Admin atau Superadmin, sehingga berpotensi dimanfaatkan untuk menghindari audit jejak digital (*audit trail evasion*).
    3. Middleware `RestrictImpersonation` sebelumnya hanya mengecek rute individual dan meloloskan aksi mutasi data finansial/integrasi kritis via HTTP `POST/PUT/DELETE` ke modul perbendaharaan (`treasury.*`), keuangan (`finance.*`), dan webhook (`admin.webhooks.*`).
- **Remediasi:**
  1. Menambahkan `session()->regenerate()` saat `take()` dan `leave()`.
  2. Memblokir penyamaran ke sesama Admin/Superadmin (`$user->isAdmin() || in_array($user->role, ['admin', 'superadmin'])`).
  3. Memperluas pemblokiran di middleware `RestrictImpersonation` untuk memblokir seluruh metode mutasi (`POST`, `PUT`, `PATCH`, `DELETE`) pada prefix rute sensitif: `admin.webhooks.`, `admin.integrations.`, `treasury.`, dan `finance.`.

```diff
// ImpersonateController.php
+        if ($user->isAdmin() || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || in_array($user->role, ['admin', 'superadmin'])) {
+            return back()->with('error', 'Anda tidak dapat menyamar sebagai sesama Admin.');
+        }
...
         Auth::login($user);
+        session()->regenerate();

// RestrictImpersonation.php
+            $restrictedPrefixes = [
+                'admin.webhooks.',
+                'admin.integrations.',
+                'treasury.',
+                'finance.',
+            ];
+            if ($currentRouteName) {
+                foreach ($restrictedPrefixes as $prefix) {
+                    if (str_starts_with($currentRouteName, $prefix) && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
+                        abort(403, 'Aksi modifikasi data sensitif/finansial tidak diizinkan dalam mode impersonate.');
+                    }
+                }
+            }
```

---

### Patch #49: Remediasi Artificial Hash Bypass via Android Hardware KeyStore RSA Asymmetric Signing pada `KeystoreManager.kt` (Android)
- **Tingkat Risiko:** Tinggi (CVSS 7.4) - Cryptographic Verification Bypass & Identity Spoofing.
- **Lokasi File:** [KeystoreManager.kt](file:///c:/project/portofolio/project-super-web/sista-android/app/src/main/java/com/sultanagung1/sista/core/security/KeystoreManager.kt)
- **Analisis Zero Trust & Zero Day:**
  - *Prinsip Zero Trust yang Dilanggar:* *"Cryptographic Hardware Root-of-Trust Must Never Be Emulated"*.
  - *Celah Keamanan:*
    - Implementasi `signChallenge(challenge)` sebelumnya menggunakan algoritma hash artifisial (`challenge.toByteArray().fold(0L) { acc, byte -> acc * 31 + byte }`) yang menghasilkan string `SIG_KEYSTORE_${hash}_${timestamp}`.
    - Pendekatan ini tidak menggunakan kunci kriptografis hardware sama sekali, mudah ditebak dan direkayasa oleh penyerang (*signature forgery*), serta tidak memenuhi standar otentikasi biometrik FIDO2 / hardware-backed keystore.
- **Remediasi:**
  1. Menginisialisasi `KeyPairGenerator` RSA 2048-bit di dalam modul hardware `AndroidKeyStore` dengan tujuan `KeyProperties.PURPOSE_SIGN or KeyProperties.PURPOSE_VERIFY`, digest `SHA256`/`SHA512`, dan padding `PKCS1`.
  2. Menyediakan fungsi `getPublicKeyPem()` untuk mengekspor public key X.509 terformat PEM ke server backend.
  3. Mengimplementasikan penandatanganan tantangan kriptografis berbasis hardware `SHA256withRSA` menggunakan `PrivateKey` dari `AndroidKeyStore`.

```diff
// KeystoreManager.kt
+        private const val KEY_ALIAS_RSA = "sulaone_auth_signing_key"
+        ...
+        val kpg = KeyPairGenerator.getInstance(KeyProperties.KEY_ALGORITHM_RSA, ANDROID_KEYSTORE)
+        val spec = KeyGenParameterSpec.Builder(KEY_ALIAS_RSA, KeyProperties.PURPOSE_SIGN or KeyProperties.PURPOSE_VERIFY)
+            .setDigests(KeyProperties.DIGEST_SHA256, KeyProperties.DIGEST_SHA512)
+            .setSignaturePaddings(KeyProperties.SIGNATURE_PADDING_RSA_PKCS1)
+            .setKeySize(2048)
+            .build()
+        kpg.initialize(spec)
+        kpg.generateKeyPair()
...
+        val privateKey = keyStore.getKey(KEY_ALIAS_RSA, null) as? PrivateKey
+        val signature = Signature.getInstance("SHA256withRSA")
+        signature.initSign(privateKey)
+        signature.update(challenge.toByteArray(Charsets.UTF_8))
+        val signatureBytes = signature.sign()
+        return Base64.encodeToString(signatureBytes, Base64.NO_WRAP)
```

---

### 🧪 Verifikasi Pengujian Otomatis Keamanan (Automated Security & QC Test Suite)

Seluruh celah keamanan VULN-031 s.d. VULN-040 dan standar kualitas kode telah diverifikasi dengan suite pengujian otomatis khusus:
- **Lokasi Test Suite:** [MobileApiSecurityAndQcTest.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/tests/Feature/Security/MobileApiSecurityAndQcTest.php)
- **Hasil Eksekusi:** **10 Passed, 0 Failed, 34 Assertions** (100% Lolos Uji).
```bash
php artisan test tests/Feature/Security/MobileApiSecurityAndQcTest.php
   PASS  Tests\Feature\Security\MobileApiSecurityAndQcTest
  ✓ non admin users are forbidden from mobile admin dashboard and approvals      1.28s  
  ✓ parent portal prevents cross child inspection                                0.07s  
  ✓ visualization student radar and trend prevent cross student inspection       0.07s  
  ✓ library self checkout prevents student impersonation                         0.07s  
  ✓ achievement verification enforces strict role whitelist                      0.07s  
  ✓ web push subscribe requires authentication                                   0.08s  
  ✓ ocr scan rejects svg uploads                                                 0.08s  
  ✓ anonymous teacher evaluation prevents duplicate spam                         0.07s  
  ✓ utbk submit handles null student cleanly                                     0.07s  
  ✓ submit signature rejects oversized payload                                   0.08s  

  Tests:    10 passed (34 assertions)
  Duration: 2.17s
```

Verifikasi regresi pengujian modul perpustakaan pasca-perbaikan QC-4:
```bash
php artisan test --filter=Library
  Tests:    41 passed (160 assertions)
  Duration: 5.20s
```

#### 🛡️ Verifikasi Pengujian Otomatis Zero Trust & Zero Day (Patches #44 - #49)
Seluruh mitigasi Zero Trust dan celah Zero Day (VULN-044 s.d. VULN-049) telah diverifikasi secara mendalam dengan automated test suite khusus:
- **Lokasi Test Suite:** [ZeroTrustZeroDayRemediationTest.php](file:///c:/project/portofolio/project-super-web/sistem-terpadu/tests/Feature/Security/ZeroTrustZeroDayRemediationTest.php)
- **Hasil Eksekusi:** **8 Passed, 0 Failed, 23 Assertions** (100% Lolos Uji).

```bash
php artisan test tests/Feature/Security/ZeroTrustZeroDayRemediationTest.php
   PASS  Tests\Feature\Security\ZeroTrustZeroDayRemediationTest
  ✓ cbt micro sync and essay upload enforce take policy                                                          1.29s  
  ✓ cbt attempt state machine blocks reversion                                                                   0.07s  
  ✓ cbt essay upload validates question belongs to exam                                                          0.07s  
  ✓ ssrf guard blocks cloud metadata and invalid protocols                                                       0.07s  
  ✓ ssrf guard blocks non http schemes                                                                           0.06s  
  ✓ midtrans webhook requires positive gross amount and valid signature                                          0.07s  
  ✓ offline sync validates batch count and binds user                                                            0.09s  
  ✓ impersonation regenerates session and restricts sensitive routes                                             0.11s  

  Tests:    8 passed (23 assertions)
  Duration: 2.02s
```

#### 🚀 Verifikasi Pengujian Regresi Penuh Seluruh Modul Keamanan (Full Security Regression Suite)
Seluruh rangkaian pengujian keamanan (`tests/Feature/Security`) dijalankan bersamaan untuk memastikan bahwa implementasi Zero Trust tidak menyebabkan regresi fungsional (*zero regression*):
```bash
php artisan test tests/Feature/Security
  Tests:    140 passed (655 assertions)
  Duration: 29.24s
```

## 🏛️ Cetak Biru (Blueprint) Keamanan Web Sekolah Skala Enterprise

Sebagai bagian dari komitmen peningkatan mutu dan kualitas sistem informasi **Sistem Terpadu (SISTA) SMA Islam Sultan Agung 1 Semarang**, dokumen ini merumuskan skema cetak biru arsitektur keamanan tingkat profesional dan enterprise. Cetak biru ini dirancang untuk melindungi data sensitif siswa, rekam medis, dan transaksi keuangan sekolah baik dari ancaman luar (perimeter) maupun ancaman dari dalam (internal).

```mermaid
graph TD
    subgraph Perimeter Keamanan Eksternal (Cloudflare Enterprise)
        Internet --> CloudflareWAF["Cloudflare WAF & DDoS Mitigation"]
        CloudflareWAF --> SSL_TLS["TLS 1.3 & HSTS Enforcer"]
        SSL_TLS --> DNSSEC["DNSSEC / SPF / DKIM / DMARC Validation"]
    end

    subgraph Server Boundary (Reverse Proxy & API Gateway)
        DNSSEC --> NginxProxy["Nginx Reverse Proxy (Rate Limiter & IP Whitelisting)"]
    end

    subgraph Internal Application & Data Defense (Laravel Core)
        NginxProxy --> AppRBAC["Zero Trust Role-Based Access Control (Policies/Gates)"]
        AppRBAC --> CryptEngine["AES-256-GCM Database Column-Level Encryption"]
        AppRBAC --> MFA["Mandatory Multi-Factor Authentication (TOTP / WebAuthn)"]
    end

    subgraph Storage & Database Layer
        CryptEngine --> EncryptedDB["PostgreSQL / MySQL (Encrypted at Rest)"]
        MFA --> SecurityLogs["Tamper-Proof Audit Trail (ELK / Wazuh SIEM)"]
    end
```

---

### 1. Perimeter Keamanan Eksternal (External Security Boundaries)
Perimeter luar berfungsi sebagai garda terdepan untuk menepis serangan siber sebelum masuk menyentuh server aplikasi utama sekolah.

#### A. Web Application Firewall (WAF) & DDoS Mitigation **[Selesai ✅]**
- **Rekomendasi Solusi**: Implementasi **Cloudflare Enterprise** atau **AWS WAF** (Level Edge) & **AdminIpWhitelistMiddleware** (Level Aplikasi).
- **Skema Implementasi**:
  - Mengaktifkan perlindungan DDoS Layer 3/4 (Network) dan Layer 7 (Application) untuk menyaring trafik tidak sah (*botnets*, *crawlers*, *brute-force*).
  - Menerapkan aturan WAF OWASP Top 10 secara ketat guna memblokir injeksi SQL, Cross-Site Scripting (XSS), dan Remote File Inclusion (RFI) di level edge server.
  - Melakukan *geo-blocking* atau *rate limiting* serta IP Whitelisting (`AdminIpWhitelistMiddleware.php`) untuk mencegah akses dari bot asing mencurigakan.

#### B. Otentikasi Email & DNS Security (Anti-Phishing) **[Selesai ✅]**
- **Rekomendasi Solusi**: Konfigurasi DNS Sekolah (`sultanagung1.sch.id`) & Otomatisasi Audit via `php artisan security:check-dns`.
- **Skema Implementasi**:
  - **DNSSEC (DNS Security Extensions)**: Mengaktifkan DNSSEC untuk mencegah serangan *DNS Cache Poisoning* dan pembajakan rute DNS (*DNS Spoofing*).
  - **SPF (Sender Policy Framework)**: Mendefinisikan IP server pengirim email sekolah yang sah guna mencegah pemalsuan identitas email (*email spoofing*).
  - **DKIM (DomainKeys Identified Mail)**: Menambahkan tanda tangan digital pada header email keluar untuk menjamin integritas isi email.
  - **DMARC (Domain-based Message Authentication, Reporting, and Conformance)**: Mengatur kebijakan DMARC ke tingkat `reject` atau `quarantine` jika email dari domain sekolah gagal validasi SPF/DKIM, guna meminimalisir penipuan (phishing) bertema PPDB atau SPP kepada orang tua siswa.
  - **Skrip Auditor Otomatis**: Menjalankan `CheckDnsSecurityCommand.php` untuk memvalidasi kepatuhan record DNS secara berkala.

#### C. SSL/TLS Modern & HTTP Security Headers **[Selesai ✅]**
- **Rekomendasi Solusi**: Konfigurasi TLS server web (Nginx/Apache).
- **Skema Implementasi**:
  - Menolak seluruh koneksi di bawah **TLS 1.2** dan mewajibkan penggunaan **TLS 1.3** dengan Cipher Suite modern (misal: `ECDHE-ECDSA-AES256-GCM-SHA384`).
  - Mengaktifkan **HSTS (HTTP Strict Transport Security)** dengan masa aktif minimal 1 tahun (`max-age=31536000; includeSubDomains; preload`) untuk mencegah serangan *downgrade* SSL (*Man-in-the-Middle*).
  - Menambahkan security headers penting pada respon HTTP aplikasi:
    ```http
    Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-random' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
    X-Frame-Options: DENY
    X-Content-Type-Options: nosniff
    Referrer-Policy: strict-origin-when-cross-origin
    Permissions-Policy: geolocation=(), camera=(), microphone=()
    ```

---

### 2. Pertahanan Aplikasi & Data Internal (Internal Application & Data Defense)
Pertahanan dari dalam bertujuan meminimalkan risiko apabila penyerang berhasil menembus perimeter luar atau jika terjadi ancaman dari aktor internal (insider threat).

#### A. Zero Trust Role-Based Access Control (RBAC) & IP Whitelisting **[Selesai ✅]**
- **Skema Implementasi**:
  - Seluruh akses administratif (misal: menu HRD Payroll, Keuangan Executive, Integrasi WhatsApp Gateway) wajib menggunakan verifikasi ganda.
  - Menerapkan **IP Whitelisting** (`AdminIpWhitelistMiddleware`) di level web server sehingga rute `/admin/*` hanya dapat diakses melalui alamat IP publik kantor sekolah SMA Islam Sultan Agung 1 Semarang atau koneksi VPN internal sekolah.
  - Membatasi delegasi wewenang secara bertingkat: guru biasa tidak boleh membaca laporan keuangan atau data siswa kelas lain secara langsung, melainkan harus mendapat delegasi resmi dari kepala sekolah atau waka kesiswaan.

#### B. Multi-Factor Authentication (MFA / 2FA) yang Wajib **[Selesai ✅]**
- **Skema Implementasi**:
  - Mengintegrasikan otentikasi dua faktor berbasis **TOTP (Time-based One-Time Password)** menggunakan Google Authenticator/Microsoft Authenticator untuk semua pengguna dengan hak akses administratif (Admin, Superadmin, Staf Keuangan/TU, Kepala Sekolah).
  - Menyediakan opsi otentikasi fisik tanpa sandi menggunakan standar **WebAuthn (FIDO2 / YubiKey)** untuk proteksi tingkat tinggi bagi administrator sistem utama.

#### C. Enkripsi Kolom Database Tingkat Lanjut (Column-Level Encryption) **[Selesai ✅]**
- **Skema Implementasi**:
  - Menggunakan fitur enkripsi kolom bawaan Laravel (`casts: encrypted` menggunakan AES-256-GCM) untuk mengamankan data sensitif siswa dan staf:
    - Nomor Rekening Bank dan Slip Gaji Guru (Modul HRD).
    - Rekam Medis UKS dan Riwayat Screening Siswa (Modul UKS).
    - Catatan Konseling BK Siswa yang bersifat rahasia (Modul Counseling).
  - Melakukan enkripsi pada level penyimpanan disk database (*Encryption-at-Rest*) pada server database PostgreSQL/MySQL untuk mencegah pencurian data jika berkas fisik database bocor.

#### D. Tamper-Proof Audit Logging & SIEM Integration **[Selesai ✅]**
- **Skema Implementasi**:
  - Log aktivitas tidak boleh disimpan hanya di database lokal (karena admin nakal dapat memodifikasinya). Log harus diekspor secara real-time ke log collector eksternal.
  - Mengirimkan log keamanan Laravel (failed login attempts, role changes, financial modifications) ke SIEM platform (seperti **Wazuh** atau **Elasticsearch/Logstash/Kibana (ELK)**) menggunakan protokol log terenkripsi.
  - Mengaktifkan notifikasi instan via Telegram/WhatsApp khusus kepada tim IT sekolah jika terjadi anomali, seperti login admin di luar jam kerja sekolah (misal: jam 2 malam) atau pencatatan transfer dana besar.

---

### 3. DevSecOps & Siklus Hidup Keamanan (Security Lifecycle)
Proses pengembangan sistem informasi sekolah harus mengintegrasikan pengujian keamanan secara otomatis guna mendeteksi bug baru secara dini.

#### A. Static Application Security Testing (SAST) & Dependency Audit **[Selesai ✅]**
- **Skema Implementasi**:
  - Mengintegrasikan perkakas analisis statis **Larastan** (PHPStan tingkat lanjut) dan **Snyk/GitHub Dependabot** ke dalam alur kerja CI/CD (GitHub Actions).
  - Setiap perubahan kode baru (*Pull Request*) secara otomatis akan diperiksa untuk mendeteksi:
    - Penggunaan dependensi PHP (Composer packages) yang memiliki kerentanan keamanan yang terdaftar (CVE).
    - Pola penulisan kode berisiko (raw query SQL rawan SQLi, manipulasi path rawan Path Traversal).
  - Pembuatan *build* ke server staging/production akan diblokir otomatis jika ditemukan kerentanan dengan tingkat keparahan tinggi (*High/Critical*).

#### B. Dynamic Application Security Testing (DAST) harian **[Selesai ✅]**
- **Skema Implementasi**:
  - Menjalankan perkakas scanning otomatis seperti **OWASP ZAP** secara berkala (seminggu sekali) terhadap server staging/pengembangan untuk mendeteksi kesalahan konfigurasi server web, kerentanan SSL, dan kebocoran informasi (*information disclosure*).

---

### 4. Strategi Pemulihan Bencana & Business Continuity (BCP / DRP)
Sekolah harus siap menghadapi skenario terburuk, termasuk kehilangan server akibat ransomware atau kegagalan perangkat keras lokal.

#### A. Kebijakan Pencadangan Data Tiga Arah (3-2-1 Backup Strategy) **[Selesai ✅]**
- **Skema Implementasi**:
  - Menyimpan minimal **3** salinan data SISTA.
  - Menggunakan **2** jenis media penyimpanan berbeda (server database lokal dan cloud storage).
  - Menyimpan **1** salinan cadangan di luar lokasi (*off-site*) cloud storage terpisah.
- **Teknis Implementasi**:
  - Menggunakan cron schedule harian (`php artisan backup:secure`) untuk mencadangkan database dan direktori berkas publik (`storage/app/public`).
  - Seluruh file cadangan wajib di-zip menggunakan proteksi kata sandi kuat dan diunggah secara otomatis ke **Amazon S3 Glacier** atau **Backblaze B2** dengan fitur **Object Lock (WORM - Write Once, Read Many)** diaktifkan selama 30 hari. Ini menjamin berkas cadangan di cloud tidak dapat dihapus atau dienkripsi ulang oleh penyerang, bahkan jika server utama terinfeksi ransomware.

#### B. Skenario Pemulihan Bencana (Disaster Recovery Plan) **[Selesai ✅]**
- **Skema Implementasi**:
  - Melakukan simulasi latihan pemulihan data sistem (*Restore Drill*) secara berkala (`php artisan backup:restore-test`) setiap 6 bulan sekali pada server replika darurat.
  - Menyusun dokumentasi petunjuk instalasi ulang sistem (*Runbook*) dari nol secara terperinci untuk meminimalisir waktu mati sistem (*downtime*) maksimal 4 jam jika terjadi kegagalan server total.

---
*(Akhir Dokumen Laporan)*

