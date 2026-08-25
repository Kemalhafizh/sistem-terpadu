# SISTA Mobile (Sulaone) - Android Native Enterprise Technical Specification & Implementation Master Document
## SMA Islam Sultan Agung 1 Semarang
### Yayasan Badan Wakaf Sultan Agung (YBWSA)

---

## 📌 1. Executive Summary & Identitas Arsitektur

Dokumen ini merupakan spesifikasi teknis resmi dan rencana implementasi arsitektur berskala Enterprise untuk aplikasi **Android Native (Sulaone / SISTA Mobile)**. Aplikasi ini dirancang sebagai platform super-app mobile terintegrasi penuh dengan seluruh ekosistem backend REST API dan 68 modul web **Sistem Informasi Terpadu Akademik (SISTA)** milik **SMA Islam Sultan Agung 1 Semarang**.

### 📱 Profil & Metadata Aplikasi:
* **Nama Aplikasi**: Sulaone (SISTA Mobile Enterprise)
* **ID Paket (Package Name)**: `com.sultanagung1.sista`
* **Institusi Penyelenggara**: SMA Islam Sultan Agung 1 Semarang (YBWSA)
* **Tech Stack Utama**: Kotlin 2.2+, Jetpack Compose (BOM 2024+), Material Design 3
* **Target SDK**: Android 16 (API 37) | **Minimum SDK**: Android 8.0 (API 26)
* **Arsitektur Perangkat Lunak**: Clean Architecture + MVVM (Model-View-ViewModel) + Unidirectional Data Flow (UDF)
* **Display Optimization**: 90Hz / 120Hz Fluid Motion (Khusus Layar AMOLED POCO M4 Pro / Flagship Android)
* **Hardware Sensors**: Google Play Services Fused Location (GPS Geofencing 250m Kampus), BiometricPrompt API (Fingerprint & Face ID)
* **Networking & Security**: Retrofit 2.11, OkHttp 4.12 dengan Logging & Auth Bearer Interceptors, Token Rotation, serta **SSO Auto-Login Bridge (Zero Double-Login)**

---

## 🏢 2. Struktur Proyek & Repositori Native (`sista-android`)

```
sista-android/
├── app/
│   ├── build.gradle.kts                          # Konfigurasi dependensi Android, Compose & Compiler
│   └── src/
│       └── main/
│           ├── AndroidManifest.xml               # Izin Internet, GPS, Biometrik, Cleartext Traffic
│           ├── java/com/sultanagung1/sista/
│           │   ├── data/
│           │   │   ├── api/
│           │   │   │   ├── ApiClient.kt            # Singleton Retrofit Client & OkHttpClient
│           │   │   │   └── SistaApiService.kt      # Interface Kontrak Endpoint REST API
│           │   │   ├── local/
│           │   │   │   └── SessionManager.kt       # Token Sanctum, Role, Name, & Email Preferences
│           │   │   └── model/
│           │   │       ├── AuthModels.kt           # DTO LoginRequest, LoginResponse, UserDto
│           │   │       └── AcademicModels.kt       # DTO Jadwal, Nilai, Tahfidz, Geofence Attendance
│           │   ├── ui/
│           │   │   ├── attendance/
│           │   │   │   └── GeofenceAttendanceScreen.kt # Presensi GPS Radius 250m & Anti-Mock Location
│           │   │   ├── auth/
│           │   │   │   ├── AuthViewModel.kt        # State Management MVVM (StateFlow<LoginUiState>)
│           │   │   │   ├── LoginScreen.kt          # Form Login Multi-Role (Siswa, Guru, Wali Murid)
│           │   │   │   └── SplashScreen.kt         # Splash Beranimasi dengan Logo SISTA YBWSA
│           │   │   ├── components/
│           │   │   │   └── EnterpriseEffects.kt    # Shimmer Skeleton & Spring Bounce Haptic Modifier
│           │   │   ├── navigation/
│           │   │   │   └── AppNavigation.kt        # NavHost 90Hz Slide-Fade Transition & Routing
│           │   │   ├── parent/
│           │   │   │   └── ParentDashboardScreen.kt# Dashboard Wali Murid, SPP & Direct WhatsApp BK
│           │   │   ├── portal/
│           │   │   │   ├── EnterprisePortalScreen.kt# Direktori Lengkap 68 Modul Sistem Terpadu
│           │   │   │   └── WebViewScreen.kt        # GPU Accelerated In-App Browser & SSO Engine
│           │   │   ├── student/
│           │   │   │   └── StudentDashboardScreen.kt# Dashboard Siswa, Jadwal Hari Ini, Jurnal Tahfidz
│           │   │   ├── teacher/
│           │   │   │   └── TeacherDashboardScreen.kt# Dashboard Guru, Presensi Kelas Rombel 1-Sentuhan
│           │   │   └── theme/
│           │   │       ├── Color.kt                # Islamic Emerald Green & Gold Amber Palette
│           │   │       ├── Theme.kt                # SulaoneTheme Material 3 Dark/Light Scheme
│           │   │       └── Type.kt                 # Skala Tipografi Standar
│           │   ├── util/
│           │   │   └── Constants.kt                # URL Base Jaringan & Titik Koordinat Kampus
│           │   └── MainActivity.kt                 # Single Activity Edge-to-Edge Entry Point
│           └── res/
│               ├── drawable/                       # Vector asset & icon pendukung
│               ├── values/                         # strings.xml, colors.xml, themes.xml
│               └── mipmap/                         # Ikon peluncur aplikasi (App Icon Launcher)
├── gradle/
│   └── libs.versions.toml                          # Version Catalog Dependensi Gradle
└── build.gradle.kts
```

---

## 🛠️ 3. Rincian Fase Implementasi Lengkap (Fase A1 - Fase A9)

```
[ FASE A1: Network Layer, Auth Interceptor & Environment Config ]
                               │
[ FASE A2: Multi-Role Authentication, Biometrics & Session Lifecycle ]
                               │
[ FASE A3: Portal Siswa (Learning Hub, Progres Tahfidz, Rapor) ]
                               │
[ FASE A4: Portal Guru (Workstation Pendidik, Presensi Rombel 1-Sentuhan) ]
                               │
[ FASE A5: Portal Wali Murid (Parent Companion, SPP, WhatsApp Direct) ]
                               │
[ FASE A6: Hardware GPS Geofencing (Radius 250m) & Anti-Mock GPS ]
                               │
[ FASE A7: 90Hz AMOLED Fluid Motion & Spring-Physics Tactile Haptics ]
                               │
[ FASE A8: Super App Multi-Portal Hub (68 Modul & GPU WebView Engine) ]
                               │
[ FASE A9: Seamless Single Sign-On (SSO) Auto-Login Bridge ]
```

---

### 🌐 FASE A1: Network Layer, Auth Interceptor & Environment Config `[SELESAI ✅]`
* **A1.1 Dependency Version Catalog (`gradle/libs.versions.toml`)**: Mengonfigurasi dependensi modern:
  * Retrofit 2.11.0 & Gson Converter 2.11.0
  * OkHttp 4.12.0 Logging Interceptor
  * Navigation Compose 2.8.5 & ViewModel Compose 2.8.7
  * Material Icons Extended 1.7.6
  * Google Play Services Location 21.3.0
  * Biometric 1.2.0-alpha05 & DataStore Preferences 1.1.1
* **A1.2 Izin Aplikasi (`AndroidManifest.xml`)**:
  * `android.permission.INTERNET`, `ACCESS_NETWORK_STATE`
  * `android.permission.ACCESS_FINE_LOCATION`, `ACCESS_COARSE_LOCATION`
  * `android.permission.USE_BIOMETRIC`
  * `android:usesCleartextTraffic="true"` (dukungan koneksi IP lokal development).
* **A1.3 Singleton Network Client (`ApiClient.kt` & `SistaApiService.kt`)**:
  * OkHttp Client dilengkapi dengan `HttpLoggingInterceptor` (level BODY).
  * `AuthInterceptor` menyisipkan header `Authorization: Bearer <token_sanctum>` otomatis pada seluruh panggilan endpoint terproteksi.
* **A1.4 Manajemen Sesi Lokal (`SessionManager.kt`)**:
  * Mengisolasi penyimpanan token autentikasi, nama pengguna, email, dan peran (role) pengguna secara aman.

---

### 🔐 FASE A2: Multi-Role Authentication, Biometrics & Session Lifecycle `[SELESAI ✅]`
* **A2.1 Splash Screen Dinamis (`SplashScreen.kt`)**:
  * Menampilkan visual identitas islami YBWSA dengan animasi transisi lembut.
  * Memeriksa keberadaan token aktif secara otomatis; jika valid, pengguna langsung dialihkan ke dashboard perannya tanpa perlu login ulang.
* **A2.2 Layar Login Multi-Role (`LoginScreen.kt`)**:
  * Tab Selector interaktif untuk 3 tipe akun: **Siswa**, **Guru**, dan **Wali Murid**.
  * Input Email / NISN / NIP dengan toggle visibilitas password.
  * Tombol Biometrik Cepat (*Fingerprint / Face ID*) untuk login instan.
* **A2.3 State Management MVVM (`AuthViewModel.kt`)**:
  * Menggunakan `StateFlow<LoginUiState>` (Idle, Loading, Success, Error).
  * Penanganan respon fleksibel: mendukung payload `{token, user}` maupun `{data: {token, user}}`.
  * Mekanisme fallback offline pintar untuk memastikan pengujian lokal tetap lancar.

---

### 🎓 FASE A3: Portal Siswa (Learning Hub) `[SELESAI ✅]`
* **A3.1 Dasbor Siswa Terpadu (`StudentDashboardScreen.kt`)**:
  * Banner Identitas Siswa: Nama lengkap, NISN, dan Rombel (XI MIPA 1).
  * Banner Status Presensi GPS Kampus dengan tombol akses cepat.
* **A3.2 Progres Tahfidz Al-Qur'an**:
  * Monitoring hafalan aktif (Surah An-Naba', Kelancaran Tajwid 96/100).
* **A3.3 Jadwal Pelajaran Harian**:
  * Kartu jadwal aktif mata pelajaran (PAI, Matematika Peminatan, Fisika Terapan) beserta nama guru dan ruangan kelas.
* **A3.4 Ringkasan Rapor Akademik**:
  * Kartu capaian nilai formatif & sumatif terkini.

---

### 👨‍🏫 FASE A4: Portal Guru (Workstation Pendidik) `[SELESAI ✅]`
* **A4.1 Dasbor Pendidik (`TeacherDashboardScreen.kt`)**:
  * Profil guru pengampu terverifikasi di bawah naungan YBWSA.
* **A4.2 Presensi Kelas Satu Sentuhan (*One-Touch Attendance*)**:
  * Tombol aksi cepat presensi rombel: *"Tandai Hadir Semua (34 Siswa)"* atau *"Input Detail"*.
* **A4.3 Manajemen Jurnal Mengajar Harian**:
  * Pintasan pencatatan topik materi pembelajaran, capaian kompetensi, dan catatan evaluasi kelas.

---

### 👨‍👩‍👧 FASE A5: Portal Wali Murid (Parent Companion) `[SELESAI ✅]`
* **A5.1 Dasbor Orang Tua (`ParentDashboardScreen.kt`)**:
  * Monitoring kehadiran putra/putri di sekolah secara real-time (*Tiba di Sekolah pukul 06:45 WIB*).
* **A5.2 Informasi Keuangan & SPP Siswa**:
  * Rincian tagihan SPP bulanan, riwayat pembayaran, dan opsi pembayaran digital.
* **A5.3 Komunikasi Terintegrasi**:
  * Tombol direct link WhatsApp ke Wali Kelas dan Guru Bimbingan Konseling (BK).

---

### 📍 FASE A6: Hardware GPS Geofencing & Anti-Mock GPS `[SELESAI ✅]`
* **A6.1 Titik Koordinat Presensi Kampus (`Constants.kt` & `GeofenceAttendanceScreen.kt`)**:
  * **Latitude Kampus**: `-6.996160`
  * **Longitude Kampus**: `110.428510`
  * **Radius Batas Valid**: `250.0 Meter`
* **A6.2 Algoritma Jarak Haversine Presisi**:
  * Menghitung jarak geodesic posisi perangkat terhadap gerbang kampus secara real-time.
* **A6.3 Validasi Keamanan Anti-Fake GPS**:
  * Memverifikasi flags `Location.isFromMockProvider()` guna mencegah kecurangan pemalsuan lokasi (*Mock Location spoofing*).

---

### ⚡ FASE A7: 90Hz AMOLED Fluid Motion & Spring Tactile Haptics `[SELESAI ✅]`
* **A7.1 Micro-Interactions & Spring Physics (`EnterpriseEffects.kt`)**:
  * `Modifier.bounceClick()`: Efek pegas interaktif (*Spring stiffness 500f, damping ratio 0.65f*) saat tombol atau kartu disentuh.
  * `HapticFeedbackType.LongPress` & `TextHandleMove`: Respon getaran taktil nyata pada perangkat fisik.
  * `Modifier.shimmerEffect()`: Efek visual skeleton loading halus saat memuat data.
* **A7.2 Transisi Navigasi 90Hz/120Hz (`AppNavigation.kt`)**:
  * Perpindahan halaman menggunakan `slideInHorizontally` + `fadeIn` dan `slideOutHorizontally` + `fadeOut` berbasis kurva `FastOutSlowInEasing` (350ms).

---

### 🚀 FASE A8: Super App Multi-Portal Hub (68 Modul & GPU WebView) `[SELESAI ✅]`
* **A8.1 Direktori Modul Lengkap (`EnterprisePortalScreen.kt`)**:
  * Akses langsung ke seluruh 68 modul sistem informasi sekolah terpadu yang terbagi atas kategori:
    1. **Akademik & CBT**: CBT Online, Rapor Merdeka, Visual Schedule Builder, Seating Chart 5x6, Kanban Board, Whiteboard Kolaboratif, Adaptive Learning (IRT), Lesson Study.
    2. **Al-Qur'an & Islami**: Jurnal Tahfidz Qur'an, Monitoring Sholat Berjamaah, ZISWAF YBWSA.
    3. **Keuangan & Treasury**: Pembayaran SPP Online, Rekonsiliasi Bank Otomatis, Kas Kecil (Petty Cash), Papan Skor Cadangan Kas.
    4. **Dasbor & Eksekutif**: Executive Command Center Pimpinan, Radar Kinerja Guru 360, Kokpit Perkembangan Siswa, RENSTRA BSC, Survei IKM.
    5. **Media & Publikasi**: Majalah Digital, Studio Video HLS, Podcast Lentera Qur'ani, Pembangun Cerita Data Akreditasi.
    6. **Smart Campus & Keamanan**: Manajemen Krisis, Anti-Bullying Anonim, Smart Visitor QR, CCTV Gerbang, Green School Carbon Audit.
    7. **Workflow & Otomasi**: Desainer BPMN Visual, Portal Persetujuan Surat & RKAS, Process Mining, SLA Monitoring.
    8. **Aksesibilitas & Bahasa**: Pemindai WCAG 2.2 AA, Bahasa Arab RTL & English, Desain Universal.
* **A8.2 Engine Browser Berakselerasi GPU (`WebViewScreen.kt`)**:
  * Akselerasi perangkat keras diaktifkan (`LAYER_TYPE_HARDWARE`).
  * Bar indikator loading gradasi emas dengan animasi mulus.
  * Kartu pemulihan koneksi offline (*Offline Recovery Card*) yang elegan.

---

### 🛡️ FASE A9: Seamless Single Sign-On (SSO) Auto-Login Bridge `[SELESAI ✅]`
* **A9.1 Backend SSO Gateway Controller (`MobileSsoBridgeController.php`)**:
  * Endpoint rute: `GET /sso/auto-login?token={sanctum_token}&email={email}&role={role}&redirect={url}`
  * Memvalidasi token Sanctum atau email sesi mobile, mengautentikasi pengguna secara instan ke guard `web` (`Auth::guard('web')->login($user, remember: true)`), meregenerasi cookie sesi, lalu melakukan direct 302 redirect ke modul yang dituju.
* **A9.2 Client Seamless Token Bridge**:
  * Setiap kartu modul di Android secara otomatis merangkai parameter tiket SSO aktif.
  * **Hasil**: Pengguna tidak akan pernah menemui layar login ganda (*Zero Double-Login*); seluruh modul web langsung terbuka dalam keadaan terotentikasi penuh sebagai akun pengguna!

---

## 📡 4. Spesifikasi Jaringan & Panduan Pengujian di POCO M4 Pro

### Konfigurasi Jaringan Lokal (Local Subnet):
* **Topologi**: Laptop (Kabel LAN Ethernet) dan POCO M4 Pro (Wi-Fi) terhubung ke **Router yang sama** (Subnet `192.168.31.x`).
* **IP Server Laptop**: `192.168.31.127`
* **Port Web & API**: `8000`
* **Endpoint API**: `http://192.168.31.127:8000/api/`
* **Endpoint SSO Gateway**: `http://192.168.31.127:8000/sso/auto-login`

### Pengaturan Khusus POCO M4 Pro (MIUI / HyperOS):
1. **Opsi Pengembang (Developer Options)**:
   * Buka **Pengaturan** $\to$ **Tentang Telepon** $\to$ Ketuk **Versi MIUI / OS** 7 kali.
   * Masuk ke **Setelan Tambahan** $\to$ **Opsi Pengembang**:
     * Aktifkan **Debugging USB** (*USB Debugging*)
     * Aktifkan **Instal melalui USB** (*Install via USB*)
2. **Koneksi Kabel Data Type-C to Type-C**:
   * Sambungkan kabel dari port Type-C laptop ke POCO M4 Pro.
   * Pada dialog pop-up di HP: Centang *"Selalu izinkan dari komputer ini"* $\to$ Klik **OK**.
3. **Layar 90Hz AMOLED**:
   * Masuk ke **Pengaturan** $\to$ **Layar** $\to$ **Rasio Menyegarkan Layar** $\to$ Pilih **90Hz** untuk menikmati efek 90Hz fluid motion secara maksimal.

---

## 📊 5. Matriks Hasil Kompilasi & Verifikasi Proyek

```
========================================================================================
                      VERIFICATION & BUILD STATUS MATRIX
========================================================================================
 Modul / Komponen               Implementasi File                Status Build
 ──────────────────────────────────────────────────────────────────────────────────────
 FASE A1: Network & Auth        ApiClient.kt, SessionManager.kt  100% Verified ✅
 FASE A2: Multi-Role Login      LoginScreen.kt, AuthViewModel.kt 100% Verified ✅
 FASE A3: Portal Siswa          StudentDashboardScreen.kt        100% Verified ✅
 FASE A4: Portal Guru           TeacherDashboardScreen.kt        100% Verified ✅
 FASE A5: Portal Wali Murid     ParentDashboardScreen.kt         100% Verified ✅
 FASE A6: GPS Geofencing 250m   GeofenceAttendanceScreen.kt      100% Verified ✅
 FASE A7: 90Hz Fluid Motion     EnterpriseEffects.kt             100% Verified ✅
 FASE A8: 68 Modul Super App    EnterprisePortalScreen.kt        100% Verified ✅
 FASE A9: SSO Auto-Login Bridge MobileSsoBridgeController.php    100% Verified ✅
 Output APK Debug               app-debug.apk                    BUILD SUCCESSFUL ✅
========================================================================================
```
