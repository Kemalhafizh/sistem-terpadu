# 🏗️ Arsitektur & Pola Desain Sistem SISTA

Dokumen ini menjelaskan struktur arsitektural kelas dunia yang diimplementasikan pada **SISTA** untuk memastikan performa tinggi, skalabilitas SaaS, serta isolasi data mutlak.

---

## 🗂️ 1. Pola Desain Utama (Design Patterns)

SISTA memisahkan tanggung jawab kode secara modular menggunakan pola desain modern:

```
[ HTTP Request ] ──> [ Controller ] ──> [ Policy / Gate ]
                               │
                               ▼ (Delegate complex rules)
                        [ Service Class ] ──> [ Database Transaction ]
                               │
                               ▼ (Data manipulation)
                        [ Eloquent Model ] ──> [ Database File ]
```

### A. Service Pattern
Untuk menghindari *fat controllers* dan menjaga prinsip *Single Responsibility Principle* (SRP), logika bisnis yang kompleks diisolasi ke dalam **Service Class** khusus di bawah `app/Services/`. 
*   **Contoh:** `SmartAlertService` (analitik prediktif), `WorkflowService` (approval engine), `SecurityMonitorService` (SIEM logs), dan `ReportBuilderService` (penyusun query ad-hoc).

### B. Polymorphic Workflows
Approval engine menggunakan relasi polimorfik Eloquent (`MorphTo`) agar tabel `workflow_requests` dapat mengikat model data apa pun (`LeaveRequest`, `Letter`, atau `Syllabus`) tanpa perlu membuat skema tabel persetujuan tersendiri untuk setiap fitur baru.

---

## 🏢 2. Arsitektur Multi-Tenancy (Multi-Database SQLite)

SISTA menggunakan arsitektur **Multi-Database per Tenant (Sekolah)** untuk menjamin isolasi data mutlak. Setiap tenant yang terdaftar melalui Onboarding Wizard memiliki berkas database fisik SQLite mandiri.

### Diagram Alur Pendaftaran & Deploy Tenant (Sequence Diagram)

```mermaid
sequenceDiagram
    autonumber
    actor Pengguna as Pengunjung Portal (sista.test)
    participant SaaS as Onboarding Controller
    participant DB_L as Database Landlord (Central)
    participant Package as Tenancy Engine (stancl/tenancy)
    participant DB_T as Database Tenant (SQLite)

    Pengguna->>SaaS: Mengisi Wizard & Klik Deploy Subdomain (e.g. sma1)
    SaaS->>SaaS: Validasi input & Cek Duplikasi Subdomain
    SaaS->>DB_L: Simpan record Tenant & Domain (sma1.sistem-terpadu.test)
    SaaS->>Package: Memicu Pembuatan Tenant Database
    Package->>DB_T: Membuat berkas database/tenants/tenant_sma1.sqlite
    Package->>DB_T: Menjalankan Migrasi Otomatis (81 Tabel Tenant)
    Package->>DB_T: Menjalankan Seeder Otomatis (TenantSystemSeeder)
    Package->>DB_T: Menjalankan Context Tenant Run Callback (Buat Admin & Tahun Ajaran Pertama)
    SaaS->>Pengguna: Mengembalikan Kredensial & Tautan Sukses (sma1.sistem-terpadu.test/login)
```

---

## 📋 3. Diagram Alur Digital Approval Workflow Engine

Approval engine memproses pengajuan dokumen berjenjang secara berurutan berdasarkan *role-path* yang dinamis:

```mermaid
graph TD
    A["Model Pengajuan Diinisiasi<br/>(is_verified=false)"] --> B["WorkflowService::initiate()"]
    B --> C["Buat record workflow_requests<br/>dan workflow_approvals per step"]
    C --> D["Kirim notifikasi real-time ke Approver Step 1"]
    
    D --> E{"Approver Meninjau"}
    E -->|Tolak| F["WorkflowService::reject()<br/>Status = 'rejected'<br/>Workflow dihentikan otomatis<br/>Kirim Notifikasi Penolakan"]
    E -->|Setujui| G{"Apakah ini step terakhir?"}
    
    G -->|Belum| H["WorkflowService::approve()<br/>Increment current_step<br/>Kirim Notifikasi ke Approver Step Berikutnya"]
    H --> E
    
    G -->|Ya| I["WorkflowService::approve() Akhir<br/>Status = 'approved'<br/>Model Pengajuan is_verified = true<br/>Kirim Notifikasi Keberhasilan"]
```

---

## 🛠️ 4. Komunikasi Real-Time (Laravel Reverb WebSocket)

SISTA menggunakan WebSocket server bawaan **Laravel Reverb** untuk mentransmisikan sinyal secara instan dari server ke client tanpa pooling HTTP berkala:
*   **Echo Client:** resource frontend mengimpor `laravel-echo` dan `pusher-js` untuk mendengarkan (*listen*) channel WebSocket.
*   **Channel Keamanan:** `PrivateChannel('user.{id}')` mengamankan notifikasi in-app agar hanya diterima oleh pengguna yang berhak.
*   **Proctoring Monitor:** Channel `chat.*` dan channel ujian real-time memfasilitasi pengawasan langsung aktivitas siswa oleh pengawas.