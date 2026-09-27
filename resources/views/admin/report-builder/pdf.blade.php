<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $report->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 12px;
            margin: 4px 0 0 0;
            font-weight: normal;
            color: #666;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .metadata-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .metadata-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #1f2937;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 7px 6px;
            font-size: 10px;
            color: #374151;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Formal Header -->
    <div class="header">
        <h1>{{ mb_strtoupper(school_name()) }}</h1>
        <h2>SISTEM INFORMASI SEKOLAH TERPADU (SISTA)</h2>
        <div class="report-title">{{ $report->name }}</div>
    </div>

    <!-- Metadata Block -->
    <table class="metadata-table">
        <tr>
            <td style="width: 60%;">
                <strong>Deskripsi:</strong><br>
                {{ $report->description ?: 'Tidak ada deskripsi.' }}
            </td>
            <td style="width: 40%; text-align: right;">
                <strong>Dibuat Oleh:</strong> {{ $report->creator->name ?? 'Sistem' }}<br>
                <strong>Tanggal Cetak:</strong> {{ now()->format('d M Y - H:i') }} WIB<br>
                <strong>Total Record:</strong> {{ $data->count() }} Data
            </td>
        </tr>
    </table>

    <!-- Main Bordered Table -->
    <table class="data-table">
        <thead>
            <tr>
                @foreach($headings as $colKey => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($headings as $colKey => $label)
                        <td>
                            @php
                                $val = $row->{$colKey};
                                if ($colKey === 'gender') {
                                    $val = $val === 'L' ? 'Laki-laki' : 'Perempuan';
                                }
                                if (in_array($colKey, ['amount', 'remaining_balance'])) {
                                    $val = 'Rp ' . number_format($val, 0, ',', '.');
                                }
                                if ($colKey === 'status') {
                                    if ($val === 'paid') $val = 'Lunas';
                                    elseif ($val === 'partial') $val = 'Sebagian';
                                    elseif ($val === 'unpaid') $val = 'Belum Lunas';
                                }
                            @endphp
                            {{ $val ?? '-' }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" style="text-align: center; padding: 12px; color: #999;">
                        Tidak ada baris data yang cocok dengan kriteria laporan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer Page Numbering -->
    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Terpadu {{ school_short_name() }}. Halaman 1
    </div>

</body>
</html>
