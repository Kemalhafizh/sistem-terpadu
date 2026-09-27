<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Screening Kesehatan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
        }
        .meta-info {
            margin-bottom: 15px;
        }
        .meta-info table {
            width: 100%;
        }
        .meta-info td {
            padding: 2px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            float: right;
            width: 250px;
            text-align: center;
        }
        .footer .signature {
            margin-top: 60px;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Screening Kesehatan Berkala</h2>
        <h3>{{ school_name() }}</h3>
        <div style="font-size: 9px; margin-top: 5px; color: #666;">
            {{ school_contact_line(['address', 'phone'], ' • ') }}
        </div>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td style="width: 15%; font-weight: bold;">Kegiatan Screening</td>
                <td style="width: 35%;">: {{ $screening->title }}</td>
                <td style="width: 15%; font-weight: bold;">Tahun Ajaran</td>
                <td style="width: 35%;">: {{ $screening->academicYear->name ?? '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Tanggal Pelaksanaan</td>
                <td>: {{ $screening->screening_date->translatedFormat('d F Y') }}</td>
                <td style="font-weight: bold;">Total Siswa</td>
                <td>: {{ $results->count() }} orang</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 20%;">Nama Siswa</th>
                <th style="width: 10%;" class="text-center">Kelas</th>
                <th style="width: 8%;" class="text-center">TB (cm)</th>
                <th style="width: 8%;" class="text-center">BB (kg)</th>
                <th style="width: 10%;" class="text-center">Visus L / R</th>
                <th style="width: 12%;" class="text-center">Gigi/Mulut</th>
                <th style="width: 12%;">Temuan Klinis</th>
                <th style="width: 15%;">Rekomendasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($results as $index => $res)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $res->student->user->name ?? '-' }}</td>
                    <td class="text-center">{{ $res->student->classroom->name ?? '-' }}</td>
                    <td class="text-center">{{ $res->height_cm ?? '-' }}</td>
                    <td class="text-center">{{ $res->weight_kg ?? '-' }}</td>
                    <td class="text-center">{{ $res->vision_left ?? '-' }} / {{ $res->vision_right ?? '-' }}</td>
                    <td class="text-center">{{ $res->dental_status ?? '-' }}</td>
                    <td>{{ $res->findings ?? '-' }}</td>
                    <td style="color: #0d9488; font-weight: bold;">{{ $res->recommendations ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="color: #999; font-style: italic;">Belum ada data hasil screening.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Semarang, {{ now()->translatedFormat('d F Y') }}</p>
        <p>Koordinator UKS Sekolah,</p>
        <div class="signature">
            Petugas Kesehatan UKS
        </div>
    </div>

</body>
</html>
