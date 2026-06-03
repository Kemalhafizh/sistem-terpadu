<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Analitik Akademik & Nilai</title>
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
        .section-title { font-size: 12px; font-weight: bold; margin-top: 20px; border-left: 3px solid #3b82f6; padding-left: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>SMA ISLAM SULTAN AGUNG 1 SEMARANG</h2>
        <p>Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242 | Telp: (024) 6580824 | Email: info@sultanagung1.sch.id</p>
        <h3 style="margin: 15px 0 0 0; text-decoration: underline;">LAPORAN ANALITIK PRESTASI AKADEMIK & NILAI SISWA</h3>
    </div>

    <table style="width: 100%; border: 0; margin-bottom: 15px;">
        <tr>
            <td width="15%"><strong>Kategori:</strong></td>
            <td width="35%">Nilai Rata-Rata Mata Pelajaran & Kelas</td>
            <td width="15%"><strong>Tanggal Cetak:</strong></td>
            <td width="35%">{{ date('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">Top 10 Rata-Rata Nilai Mata Pelajaran</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="10%">Peringkat</th>
                <th width="60%">Mata Pelajaran</th>
                <th width="30%" style="text-align: center;">Rata-Rata Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades['top_subjects'] as $index => $sub)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $sub->name }}</strong></td>
                <td style="text-align: center; font-weight: bold;">{{ $sub->average }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Distribusi Rentang Nilai Siswa</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="70%">Rentang Nilai</th>
                <th width="30%" style="text-align: center;">Jumlah Evaluasi Siswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades['distribution'] as $range => $count)
            <tr>
                <td>Evaluasi dengan Nilai <strong>{{ $range }}</strong></td>
                <td style="text-align: center;">{{ $count }} kali</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($grades['class_averages']) && count($grades['class_averages']) > 0)
    <div class="section-title">Rata-Rata Nilai per Kelas</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="70%">Kelas</th>
                <th width="30%" style="text-align: center;">Rata-Rata Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grades['class_averages'] as $class)
            <tr>
                <td><strong>{{ $class->name }}</strong></td>
                <td style="text-align: center; font-weight: bold;">{{ $class->average }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

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
