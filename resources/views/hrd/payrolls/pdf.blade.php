<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $payroll->month_name }} {{ $payroll->year }} - {{ $payroll->employee->employee_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #111111;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }
        .school-address {
            font-size: 11px;
            color: #666666;
            margin: 0;
        }
        .slip-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 20px 0;
            color: #111111;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 130px;
            color: #555555;
        }
        .meta-value {
            color: #111111;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #dddddd;
            padding-bottom: 5px;
            margin: 25px 0 10px 0;
            color: #444444;
            letter-spacing: 0.5px;
        }
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .attendance-table th {
            background-color: #f7f7f7;
            border: 1px solid #e3e3e3;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #555555;
        }
        .attendance-table td {
            border: 1px solid #e3e3e3;
            text-align: center;
            padding: 8px;
            color: #111111;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .salary-table td {
            padding: 8px 10px;
            vertical-align: middle;
        }
        .salary-row-even {
            background-color: #fafafa;
        }
        .salary-label {
            width: 60%;
            color: #333333;
        }
        .salary-amount {
            width: 40%;
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: bold;
        }
        .salary-total-row {
            border-top: 2px solid #111111;
            font-size: 15px;
            font-weight: bold;
            background-color: #f5f5f5 !important;
        }
        .salary-total-label {
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111111;
        }
        .salary-total-amount {
            text-align: right;
            font-family: 'Courier New', Courier, monospace;
            font-size: 15px;
            font-weight: bold;
            color: #111111;
        }
        .text-green {
            color: #16a34a;
        }
        .text-red {
            color: #dc2626;
        }
        .footer-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 200px;
            text-align: center;
        }
        .signature-space {
            height: 70px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }
        .signature-title {
            font-size: 11px;
            color: #666666;
        }
        .bank-info {
            float: left;
            font-size: 11px;
            color: #555555;
            line-height: 1.6;
            margin-top: 20px;
            border: 1px dashed #cccccc;
            padding: 10px;
            border-radius: 5px;
            width: 250px;
        }
    </style>
</head>
<body>

    <!-- Header Instansi Sekolah -->
    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle;">
                    <h1 class="school-name">{{ $schoolName }}</h1>
                    <p class="school-address">
                        {{ $schoolAddress }}<br>
                        Telp: {{ $schoolPhone }} | Email: {{ $schoolEmail }}
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Judul Slip -->
    <div class="slip-title">SLIP GAJI BULANAN</div>

    <!-- Informasi Pegawai -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Pegawai</td>
            <td class="meta-value">: {{ $payroll->employee->name }}</td>
            <td class="meta-label">Bulan / Tahun</td>
            <td class="meta-value">: {{ $payroll->month_name }} {{ $payroll->year }}</td>
        </tr>
        <tr>
            <td class="meta-label">No. Pegawai (NIP)</td>
            <td class="meta-value">: {{ $payroll->employee->employee_number }}</td>
            <td class="meta-label">Status Kerja</td>
            <td class="meta-value">: {{ strtoupper($payroll->employee->employment_status) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Jabatan</td>
            <td class="meta-value">: {{ $payroll->employee->position }}</td>
            <td class="meta-label">Tanggal Cetak</td>
            <td class="meta-value">: {{ now()->translatedFormat('d F Y H:i') }} WIB</td>
        </tr>
    </table>

    <!-- Ringkasan Kehadiran -->
    <div class="section-title">Ringkasan Kehadiran</div>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Hari Kerja Efektif</th>
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Izin / Sakit / Cuti</th>
                <th>Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
            @if($payroll->hasAttendanceData())
                <tr>
                    <td>{{ $payroll->working_days }} hari</td>
                    <td class="text-green" style="font-weight: bold;">{{ $payroll->present_days }} hari</td>
                    <td class="{{ $payroll->late_days > 0 ? 'text-red' : '' }}">{{ $payroll->late_days }} hari</td>
                    <td>{{ $payroll->leave_days }} hari</td>
                    <td class="{{ $payroll->absent_days > 0 ? 'text-red' : '' }}" style="font-weight: bold;">{{ $payroll->absent_days }} hari</td>
                </tr>
            @else
                <tr>
                    <td colspan="5">{{ __('Belum ada data presensi untuk periode ini.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Rincian Gaji & Komponen Keuangan -->
    <div class="section-title">Rincian Penghasilan & Potongan</div>
    <table class="salary-table">
        <tr class="salary-row-even">
            <td class="salary-label">1. Gaji Pokok Bulanan</td>
            <td class="salary-amount">{{ $payroll->base_salary_formatted }}</td>
        </tr>
        <tr>
            <td class="salary-label">2. Tunjangan & Bonus Jabatan</td>
            <td class="salary-amount text-green">+ {{ $payroll->allowances_formatted }}</td>
        </tr>
        <tr class="salary-row-even">
            <td class="salary-label">3. Potongan Kehadiran (Terlambat & Tidak Hadir)</td>
            <td class="salary-amount text-red">- {{ $payroll->attendance_deductions_formatted }}</td>
        </tr>
        <tr>
            <td class="salary-label">4. Potongan Lain</td>
            <td class="salary-amount text-red">- {{ $payroll->other_deductions_formatted }}</td>
        </tr>
        <tr class="salary-total-row">
            <td class="salary-label salary-total-label">Gaji Bersih Diterima (Net Salary)</td>
            <td class="salary-amount salary-total-amount">{{ $payroll->net_salary_formatted }}</td>
        </tr>
    </table>

    <!-- Bagian Tanda Tangan & Keterangan Transfer -->
    <div class="footer-section">
        <!-- Informasi Pembayaran Bank -->
        <div class="bank-info">
            <strong>Keterangan Pembayaran Transfer:</strong><br>
            Bank Penerima: {{ $payroll->employee->bank_name ?? '-' }}<br>
            No. Rekening: {{ $payroll->employee->bank_account ?? '-' }}<br>
            Status Bayar: <strong>{{ strtoupper($payroll->status) }}</strong>
            @if($payroll->payment_date)
                ({{ $payroll->payment_date->translatedFormat('d M Y') }})
            @endif
        </div>

        <!-- Tanda Tangan Bendahara -->
        <div class="signature-box">
            <p>Semarang, {{ $payroll->payment_date ? $payroll->payment_date->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}</p>
            <p class="signature-title">Bendahara Sekolah,</p>
            <div class="signature-space"></div>
            <p class="signature-name">{{ signature_name(null) }}</p>
            <p class="signature-title">{{ signature_nip(null) }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
