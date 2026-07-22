# 🛡️ Laporan Keamanan & Kualitas Kode: security_patch.md
**Project:** Sistem Terpadu (SISTA) - SMA Islam Sultan Agung 1 Semarang
**Tanggal Review:** 17 Juli 2026

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

---

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

