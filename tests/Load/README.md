# Panduan Load Testing SMA Islam Sultan Agung 1 Semarang (k6)

Direktori ini berisi berkas skenario untuk melakukan pengujian beban (*load testing*) pada **Sistem Terpadu SMA ISA 1** menggunakan **k6** dari Grafana.

---

## Persyaratan Instalasi

Untuk menjalankan skenario pengujian ini, Anda perlu menginstal tools `k6` pada mesin lokal atau server pengujian Anda:

### macOS (Homebrew)
```bash
brew install k6
```

### Windows (Chocolatey atau Winget)
```powershell
choco install k6
# atau
winget install gnu.k6
```

### Linux (Debian/Ubuntu)
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

---

## Cara Menjalankan Skenario

Setiap skenario dapat dijalankan menggunakan perintah CLI `k6 run`. Anda dapat melewatkan variabel lingkungan `BASE_URL` untuk mengarahkan target ke alamat pengujian (default: `http://localhost`).

### 1. Pengujian Beban Normal (Harian)
Simulasi ~150 pengguna aktif harian secara berkesinambungan.
```bash
k6 run -e BASE_URL=http://your-test-domain.test scenarios/daily_normal.js
```

### 2. Jam Sibuk Pagi (Morning Rush)
Simulasi 730 pengguna masuk (30 guru menginput absensi + 700 siswa login masuk).
```bash
k6 run -e BASE_URL=http://your-test-domain.test scenarios/morning_rush.js
```

### 3. Hari Pembayaran SPP (SPP Payment Day)
Simulasi 500 orang tua melihat tagihan dan men-checkout gateway Midtrans.
```bash
k6 run -e BASE_URL=http://your-test-domain.test scenarios/spp_payment_day.js
```

### 4. Ujian Online (CBT Exam)
Simulasi 200 siswa aktif menjawab soal ujian CBT secara serentak dengan toleransi kesalahan 0%.
```bash
k6 run -e BASE_URL=http://your-test-domain.test scenarios/cbt_exam.js
```

### 5. Hari Pembagian Rapor (Rapor Day)
Simulasi puncak beban tertinggi yaitu 1200 pengguna mengakses dan mengunduh berkas e-rapor PDF.
```bash
k6 run -e BASE_URL=http://your-test-domain.test scenarios/rapor_day.js
```

---

## Target Metrik (Thresholds KPI)

| Skenario | Target Response Time (p95) | Target Error Rate |
| :--- | :---: | :---: |
| **daily_normal.js** | < 500ms | < 0.1% |
| **morning_rush.js** | < 1500ms (1.5s) | < 0.5% |
| **spp_payment_day.js**| < 3000ms (3.0s) | < 0.1% |
| **cbt_exam.js** | < 2000ms (2.0s) | **0% (Zero Error)** |
| **rapor_day.js** | < 3000ms (3.0s) | < 1.0% |
