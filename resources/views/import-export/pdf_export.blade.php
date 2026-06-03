<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #1e293b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 0;
        }
        .school-subtitle {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 15px 0 5px 0;
            color: #0f172a;
        }
        .doc-meta {
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #1e3a8a;
            text-align: left;
        }
        td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <!-- School Header / Kop Surat -->
    <div class="header">
        <h1 class="school-name">SMA Islam Sultan Agung 1 Semarang</h1>
        <p class="school-subtitle">Sistem Informasi Sekolah Terpadu & SaaS Platform SISTA</p>
    </div>

    <!-- Document Title -->
    <h2 class="doc-title">{{ $title }}</h2>
    <div class="doc-meta">
        Diekspor pada: {{ now()->format('d M Y, H:i') }} | Total Data: {{ count($data) }} Baris
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    @foreach($row as $val)
                        <td>{{ $val }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) + 1 }}" style="text-align: center; font-style: italic; color: #94a3b8; padding: 20px;">
                        Tidak ada data yang tersedia untuk modul ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        SMA Islam Sultan Agung 1 Semarang — Dokumen Resmi Laporan SISTA — Halaman 1 dari 1
    </div>

</body>
</html>
