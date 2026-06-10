<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi - {{ $event->name }}</title>
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
            color: #0d5c3a; /* Dark green theme representing Sultan Agung school */
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
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 10px 0 5px 0;
            color: #0f172a;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-table td {
            padding: 2px 0;
            border: none;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 8px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            text-align: center;
        }
        .summary-box th {
            padding: 6px;
            border: 1px solid #cbd5e1;
            background-color: #e2e8f0;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #0d5c3a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 6px;
            border: 1px solid #0d5c3a;
            text-align: left;
        }
        table.data-table td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .sign-area {
            margin-top: 30px;
            width: 100%;
        }
        .sign-area td {
            border: none;
            width: 50%;
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

    <!-- School Header -->
    <div class="header">
        <h1 class="school-name">SMA Islam Sultan Agung 1 Semarang</h1>
        <p class="school-subtitle">Sistem Informasi Sekolah Terpadu SISTA</p>
    </div>

    <!-- Doc Title -->
    <div class="doc-title">Laporan Presensi Kegiatan & Acara Sekolah</div>

    <!-- Meta Information -->
    <table class="meta-table">
        <tr>
            <td width="18%" class="font-bold">Nama Kegiatan:</td>
            <td width="32%">{{ $event->name }}</td>
            <td width="18%" class="font-bold">Tanggal Acara:</td>
            <td width="32%">{{ $event->event_date?->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tipe Kegiatan:</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $event->type) }}</td>
            <td class="font-bold">Waktu Pelaksanaan:</td>
            <td>
                @if($event->start_time)
                    {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}{{ $event->end_time ? ' - ' . \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }} WIB
                @else
                    Waktu Fleksibel
                @endif
            </td>
        </tr>
        <tr>
            <td class="font-bold">Target Peserta:</td>
            <td>
                @if($event->target === 'all_students')
                    Semua Murid
                @elseif($event->target === 'all_teachers')
                    Semua Guru
                @elseif($event->target === 'specific_class')
                    Kelas Tertentu
                @else
                    Grup Kustom
                @endif
            </td>
            <td class="font-bold">Penanggung Jawab:</td>
            <td>{{ $event->creator->name ?? 'Sistem' }}</td>
        </tr>
    </table>

    <!-- Statistics Summary Box -->
    <table class="summary-box">
        <thead>
            <tr>
                <th>Total Peserta</th>
                <th>Hadir</th>
                <th>Terlambat</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpa (Mangkir)</th>
                <th>Rasio Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">{{ count($records) }}</td>
                <td style="color: #15803d; font-weight: bold;">{{ $stats['hadir'] }}</td>
                <td style="color: #0f766e; font-weight: bold;">{{ $stats['terlambat'] }}</td>
                <td style="color: #b45309;">{{ $stats['izin'] }}</td>
                <td style="color: #be123c;">{{ $stats['sakit'] }}</td>
                <td style="color: #475569; font-weight: bold;">{{ $stats['alpa'] }}</td>
                <td class="font-bold" style="font-size: 12px; background-color: #f0fdf4; color: #166534;">
                    @php
                        $present = $stats['hadir'] + $stats['terlambat'];
                        $total = count($records);
                        $rate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                    @endphp
                    {{ $rate }}%
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Scores Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Lengkap</th>
                <th style="width: 15%;">Kelas / Peran</th>
                <th class="text-center" style="width: 15%;">Status</th>
                <th class="text-center" style="width: 15%;">Jam Masuk</th>
                <th>Catatan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $record)
                @php
                    $userTypeLabel = $record->user?->role === 'student' ? ($record->user?->student?->classroom?->name ?? 'Siswa') : 'Guru/Staf';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $record->user?->name }}</td>
                    <td>{{ $userTypeLabel }}</td>
                    <td class="text-center">
                        <span class="status-badge">
                            {{ strtoupper($record->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') . ' WIB' : '-' }}
                    </td>
                    <td>{{ $record->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data kehadiran peserta.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Sign-off Area -->
    <table class="sign-area">
        <tr>
            <td></td>
            <td class="text-center">
                Semarang, {{ now()->format('d M Y') }}<br>
                Penanggung Jawab Kegiatan,<br><br><br><br>
                <span class="font-bold" style="text-decoration: underline;">{{ $event->creator->name ?? 'Staf Sekolah' }}</span>
            </td>
        </tr>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        SMA Islam Sultan Agung 1 Semarang — Dokumen Laporan Resmi SISTA
    </div>

</body>
</html>
