<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Tagihan - {{ $billing->student->user->name ?? $billing->student_id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.6; font-size: 12px; }
        .invoice-container { padding: 30px; }
        .header { border-bottom: 3px double #064e3b; padding-bottom: 15px; margin-bottom: 25px; text-align: center; }
        .school-name { font-size: 20px; font-weight: bold; color: #064e3b; margin: 0; text-transform: uppercase; }
        .school-subtitle { font-size: 14px; font-weight: bold; color: #374151; margin: 2px 0 0 0; }
        .school-info { font-size: 9px; color: #666; margin: 2px 0 0 0; }
        
        .invoice-title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; text-transform: uppercase; margin-bottom: 20px; color: #111827; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table { border: none; margin-bottom: 25px; }
        .info-table td { border: none; padding: 4px 0; vertical-align: top; }
        .info-label { width: 120px; color: #4b5563; font-weight: bold; }
        .info-colon { width: 15px; text-align: center; color: #4b5563; }
        .info-value { font-weight: bold; color: #111827; }
        
        .items-table th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 8px; font-size: 10px; font-weight: bold; text-align: left; }
        .items-table td { border: 1px solid #e5e7eb; padding: 8px; vertical-align: middle; }
        
        .instructions-box { background: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; border-radius: 8px; margin-top: 30px; font-size: 10px; }
        .instructions-title { font-weight: bold; color: #064e3b; margin-bottom: 5px; text-transform: uppercase; }
        .instructions-list { margin: 0; padding-left: 15px; }
        .instructions-list li { margin-bottom: 3px; }
        
        .footer { margin-top: 50px; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-space { height: 70px; }
        .clear { clear: both; }
        
        .total-container { text-align: right; margin-top: 15px; margin-bottom: 30px; }
        .total-box { border: 2px solid #064e3b; padding: 10px 15px; display: inline-block; border-radius: 6px; }
        .total-label { font-size: 10px; color: #4b5563; font-weight: bold; text-transform: uppercase; }
        .total-value { font-size: 16px; font-weight: bold; color: #b91c1c; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- School Kop Surat -->
        <div class="header">
            <h1 class="school-name">Yayasan Badan Wakaf Sultan Agung</h1>
            <h2 class="school-subtitle">SMA ISLAM SULTAN AGUNG 1 SEMARANG</h2>
            <p class="school-info">
                Terakreditasi A (Unggul) &bull; Jl. Mataram No.657, Wonodri, Kec. Semarang Selatan, Kota Semarang, Jawa Tengah 50242<br>
                Telp: (024) 8313922 | Email: info@smaisla1smg.sch.id | Website: smaisla1smg.sch.id
            </p>
        </div>

        <div class="invoice-title">Surat Pemberitahuan Tagihan SPP & Iuran</div>

        <!-- Student & Date Info -->
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <table>
                        <tr>
                            <td class="info-label">Nama Siswa</td>
                            <td class="info-colon">:</td>
                            <td class="info-value uppercase">{{ $billing->student->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">NIS / NISN</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">{{ $billing->student->nis }} / {{ $billing->student->nisn }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Kelas</td>
                            <td class="info-colon">:</td>
                            <td class="info-value uppercase">{{ $billing->student->classroom->name ?? '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 20px;">
                    <table>
                        <tr>
                            <td class="info-label">No. Tagihan</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">INV/{{ $billing->academicYear->year }}/{{ $billing->id }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Tanggal Cetak</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">{{ date('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Tahun Ajaran</td>
                            <td class="info-colon">:</td>
                            <td class="info-value">T.A {{ $billing->academicYear->year }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Billings Details Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">NO</th>
                    <th style="width: 45%;">DESKRIPSI IURAN</th>
                    <th style="width: 20%; text-align: center;">JATUH TEMPO</th>
                    <th style="width: 15%; text-align: right;">TOTAL TAGIHAN</th>
                    <th style="width: 15%; text-align: right;">SISA TUNGGAKAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td style="font-weight: bold; text-transform: uppercase;">
                        {{ $billing->category->name }}
                        @if($billing->month)
                            <span style="color:#666; font-size: 10px;">({{ $billing->month }})</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($billing->due_date)->translatedFormat('d M Y') }}</td>
                    <td style="text-align: right;">Rp {{ number_format($billing->amount, 0, ',', '.') }}</td>
                    <td style="text-align: right; font-weight: bold; color: #b91c1c;">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Box -->
        <div class="total-container">
            <div class="total-box">
                <div class="total-label">Total Harus Dibayar (Outstanding)</div>
                <div class="total-value">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Payment Instructions Box -->
        <div class="instructions-box">
            <div class="instructions-title">Petunjuk & Prosedur Pembayaran:</div>
            <ul class="instructions-list">
                <li>Pembayaran dapat dilakukan melalui **Transfer Bank** ke rekening resmi sekolah:
                    <br>&bull; **Bank Jateng Syariah** &bull; No. Rekening: **503-222-1110** a.n. **SMA Islam Sultan Agung 1 Semarang**
                </li>
                <li>Tuliskan **Nama Lengkap Siswa, Kelas, dan NIS** pada berita transfer untuk memudahkan verifikasi manual.</li>
                <li>Setelah melakukan pembayaran, mohon unggah bukti bayar via portal siswa SISTA atau bawa bukti cetak ke petugas keuangan sekolah.</li>
                <li>Pembayaran secara langsung/cash dilayani di loket Bendahara Sekolah pada jam kerja (Senin - Jumat, pukul 07.30 - 14.30 WIB).</li>
            </ul>
        </div>

        <!-- Footer / Signature Block -->
        <div class="footer">
            <div style="float: left; font-size: 9px; color: #666; margin-top: 70px;">
                *Dokumen ini diterbitkan secara otomatis oleh sistem SISTA SMA ISA 1 Semarang.<br>
                Tanggal update status tagihan: {{ date('d-m-Y H:i') }} WIB.
            </div>
            
            <div class="signature-box">
                <p>Semarang, {{ date('d F Y') }}</p>
                <p>Kepala Urusan Tata Usaha,</p>
                <div class="signature-space"></div>
                <p><strong>Drs. H. Mulyono, M.Pd</strong></p>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
