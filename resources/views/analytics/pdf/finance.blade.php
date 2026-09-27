<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Analitik Keuangan Sekolah</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 10px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0 0; font-size: 10px; color: #666; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; }
        .main-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: left; }
        .main-table td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .signature-section { width: 100%; margin-top: 50px; text-align: right; }
        .signature-box { display: inline-block; width: 200px; text-align: center; }
        .section-title { font-size: 12px; font-weight: bold; margin-top: 20px; border-left: 3px solid #10b981; padding-left: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ mb_strtoupper(school_name()) }}</h2>
        <p>{{ school_contact_line() }}</p>
        <h3 style="margin: 15px 0 0 0; text-decoration: underline;">LAPORAN ANALITIK RASIO KEUANGAN & TUNGGAKAN SISWA</h3>
    </div>

    <table style="width: 100%; border: 0; margin-bottom: 15px;">
        <tr>
            <td width="15%"><strong>Kategori:</strong></td>
            <td width="35%">Tunggakan & Pelunasan Global & Kelas</td>
            <td width="15%"><strong>Tanggal Cetak:</strong></td>
            <td width="35%">{{ date('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Keuangan Global</div>
    <table class="main-table">
        <thead>
            <tr>
                <th>Metrik Keuangan</th>
                <th style="text-align: right;">Jumlah (Rupiah)</th>
                <th style="text-align: center;">Persentase (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Tagihan Diterbitkan</strong></td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($finance['global']['total'], 2, ',', '.') }}</td>
                <td style="text-align: center;">100%</td>
            </tr>
            <tr>
                <td style="color: #10b981;"><strong>Total Pembayaran Diterima (Lunas/Lunas Sebagian)</strong></td>
                <td style="text-align: right; font-weight: bold; color: #10b981;">Rp {{ number_format($finance['global']['paid'], 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold; color: #10b981;">{{ $finance['global']['paid_percentage'] }}%</td>
            </tr>
            <tr>
                <td style="color: #ef4444;"><strong>Total Tunggakan Belum Dibayar</strong></td>
                <td style="text-align: right; font-weight: bold; color: #ef4444;">Rp {{ number_format($finance['global']['unpaid'], 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold; color: #ef4444;">{{ $finance['global']['unpaid_percentage'] }}%</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Rincian Pembayaran per Kelas</div>
    <table class="main-table">
        <thead>
            <tr>
                <th>Kelas</th>
                <th style="text-align: right;">Total Tagihan (Rp)</th>
                <th style="text-align: right;">Sudah Dibayar (Rp)</th>
                <th style="text-align: right;">Tunggakan (Rp)</th>
                <th style="text-align: center;">Tingkat Pelunasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($finance['by_classroom'] as $class)
            <tr>
                <td><strong>{{ $class['classroom'] }}</strong></td>
                <td style="text-align: right;">Rp {{ number_format($class['total'], 2, ',', '.') }}</td>
                <td style="text-align: right; color: #10b981;">Rp {{ number_format($class['paid'], 2, ',', '.') }}</td>
                <td style="text-align: right; color: #ef4444;">Rp {{ number_format($class['unpaid'], 2, ',', '.') }}</td>
                <td style="text-align: center; font-weight: bold; color: {{ $class['paid_percentage'] >= 80 ? '#10b981' : '#f59e0b' }};">
                    {{ $class['paid_percentage'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Semarang, {{ date('d F Y') }}</p>
            <p>Kepala Sekolah,</p>
            <br><br><br>
            <p><strong>_______________________</strong></p>
            <p>NIP. -</p>
        </div>
    </div>
</body>
</html>
