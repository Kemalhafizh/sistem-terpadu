<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Mutabaah {{ $classroom->name }} - {{ $weekStart }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px double #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111111;
        }
        .header h3 {
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: normal;
            color: #555555;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .info-table td.label {
            font-weight: bold;
            width: 15%;
        }
        .info-table td.value {
            width: 35%;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .content-table th, .content-table td {
            border: 1px solid #cccccc;
            padding: 8px 6px;
            text-align: left;
        }
        .content-table th {
            background-color: #f7f7f7;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .badge-a { background-color: #d1fae5; color: #065f46; }
        .badge-b { background-color: #fef3c7; color: #92400e; }
        .badge-c { background-color: #ffedd5; color: #9a3412; }
        .badge-d { background-color: #fee2e2; color: #991b1b; }
        
        .footer-sign {
            width: 100%;
            margin-top: 50px;
        }
        .footer-sign td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 70px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rekapitulasi Mutaba'ah Siswa</h2>
        <h3>{{ school_name() }}</h3>
        <p style="margin: 5px 0 0 0; font-size: 10px; color: #666;">
            Sistem Terpadu Akademik & Keislaman (SISTA)
        </p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Kelas:</td>
            <td class="value">{{ $classroom->name }}</td>
            <td class="label">Periode Pekan:</td>
            <td class="value">
                {{ \Carbon\Carbon::parse($weekStart)->translatedFormat('d M') }} s/d {{ \Carbon\Carbon::parse($weekEnd)->translatedFormat('d M Y') }}
            </td>
        </tr>
        <tr>
            <td class="label">Wali Kelas:</td>
            <td class="value">{{ $classroom->teacher->name ?? '-' }}</td>
            <td class="label">Tanggal Cetak:</td>
            <td class="value">{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 25%;">Nama Siswa</th>
                <th style="width: 10%;" class="text-center">NISN</th>
                <th style="width: 10%;" class="text-center">Rata Skor</th>
                <th style="width: 8%;" class="text-center">Grade</th>
                <th style="width: 10%;" class="text-center">Full Shalat</th>
                <th style="width: 10%;" class="text-center">Tadarus</th>
                <th style="width: 22%;">Catatan Wali Kelas / Pembinaan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                @php
                    $summary = $summaries->get($student->id);
                    $gradeBadge = '';
                    if ($summary) {
                        $gradeBadge = 'badge-' . strtolower($summary->grade);
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-weight: bold;">{{ $student->user->name }}</td>
                    <td class="text-center">{{ $student->nisn }}</td>
                    <td class="text-center" style="font-weight: bold; font-size: 12px;">
                        {{ $summary ? $summary->total_score : '-' }}
                    </td>
                    <td class="text-center">
                        @if($summary)
                            <span class="badge {{ $gradeBadge }}">{{ $summary->grade }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $summary ? $summary->sholat_5_waktu_count . ' / 7 Hari' : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $summary ? $summary->tadarus_total_pages . ' Hal' : '-' }}
                    </td>
                    <td style="font-size: 9px; italic: true;">
                        {{ $summary ? $summary->wali_kelas_comment : '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-sign">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Sekolah {{ school_short_name() }}
                <div class="signature-space"></div>
                <strong>{{ signature_name($kepsekName ?? school_principal_name()) }}</strong><br>
                {{ signature_nip(school_principal_nip()) }}
            </td>
            <td>
                Semarang, {{ now()->translatedFormat('d F Y') }}<br>
                Wali Kelas {{ $classroom->name }}
                <div class="signature-space"></div>
                <strong>{{ $classroom->teacher->name ?? '-' }}</strong><br>
                NIP. -
            </td>
        </tr>
    </table>

</body>
</html>
