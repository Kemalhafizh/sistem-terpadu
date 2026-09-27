<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Analitik Kehadiran Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 10px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0 0; font-size: 10px; color: #666; }
        .meta-table { width: 100%; margin-bottom: 15px; border: 0; }
        .meta-table td { padding: 4px; border: 0; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: left; }
        .main-table td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .signature-section { width: 100%; margin-top: 50px; text-align: right; }
        .signature-box { display: inline-block; width: 200px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ mb_strtoupper(school_name()) }}</h2>
        <p>{{ school_contact_line() }}</p>
        <h3 style="margin: 15px 0 0 0; text-decoration: underline;">LAPORAN ANALITIK TREN KEHADIRAN SISWA</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Periode:</strong></td>
            <td width="35%">Tren 6 Bulan Terakhir</td>
            <td width="15%"><strong>Tanggal Cetak:</strong></td>
            <td width="35%">{{ date('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>Bulan</th>
                <th style="text-align: center;">Hadir (H)</th>
                <th style="text-align: center;">Sakit (S)</th>
                <th style="text-align: center;">Izin (I)</th>
                <th style="text-align: center;">Alpa (A)</th>
                <th style="text-align: center;">Total Presensi</th>
                <th style="text-align: center;">Persentase Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceTrend as $row)
            <tr>
                <td><strong>{{ $row['month'] }}</strong></td>
                <td style="text-align: center;">{{ $row['H'] }}</td>
                <td style="text-align: center;">{{ $row['S'] }}</td>
                <td style="text-align: center;">{{ $row['I'] }}</td>
                <td style="text-align: center;">{{ $row['A'] }}</td>
                <td style="text-align: center;">{{ $row['total'] }}</td>
                <td style="text-align: center; font-weight: bold; color: {{ $row['percentage'] >= 90 ? '#10b981' : '#f59e0b' }};">
                    {{ $row['percentage'] }}%
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
