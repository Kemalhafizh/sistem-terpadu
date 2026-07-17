<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Smart Alerts - SISTA</title>
    <style>
        @page {
            margin: 1.2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 12px;
            margin: 3px 0 0 0;
            text-transform: uppercase;
            font-weight: 600;
            color: #475569;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 9px;
            color: #64748b;
            font-weight: 500;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .meta-info td {
            border: none;
            padding: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            text-transform: uppercase;
            font-size: 9px;
            border: 1px solid #334155;
            letter-spacing: 0.3px;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            display: inline-block;
            text-align: center;
        }
        .badge-critical {
            background-color: #ffe4e6;
            color: #b91c1c;
            border: 1px solid #fecdd3;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .badge-active {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .badge-acknowledged {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .badge-resolved {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 20px;
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SMA ISLAM SULTAN AGUNG 1 SEMARANG</h1>
        <h2>Laporan Analisis Risiko & Smart Alerts (SISTA)</h2>
        <p>Alamat: Jl. Sukarno-Hatta No.4 Semarang | Telp: (024) 6712345</p>
    </div>

    <table class="meta-info">
        <tr>
            <td width="15%" class="bold">Dicetak Oleh:</td>
            <td width="35%">{{ auth()->user()->name }} ({{ ucwords(str_replace('_', ' ', auth()->user()->role)) }})</td>
            <td width="15%" class="bold">Tanggal Cetak:</td>
            <td width="35%">{{ now()->format('d-m-Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="bold">Total Alert:</td>
            <td>{{ count($alerts) }} Kasus</td>
            <td class="bold">Status Sistem:</td>
            <td style="color: #059669;" class="bold">Kondusif (Terpantau Berkelanjutan)</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="22%">Judul Alert / Deskripsi</th>
                <th width="12%">Kategori Isu</th>
                <th width="10%" class="text-center">Tingkat</th>
                <th width="10%" class="text-center">Status</th>
                <th width="15%">Nama Target</th>
                <th width="12%">Tgl Deteksi</th>
                <th width="15%">Penyelesaian / Eskalasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($alerts as $index => $alert)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="bold" style="margin-bottom: 4px; color: #0f172a;">{{ $alert->title }}</div>
                        <div style="font-size: 9px; color: #475569;">{{ Str::limit($alert->description, 100) }}</div>
                    </td>
                    <td>
                        {{ ucwords(str_replace('_', ' ', $alert->type)) }}
                    </td>
                    <td class="text-center">
                        @if($alert->severity === 'critical')
                            <span class="badge badge-critical">🚨 Kritis</span>
                        @else
                            <span class="badge badge-warning">⚠️ Peringatan</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($alert->status === 'resolved')
                            <span class="badge badge-resolved">Selesai</span>
                        @elseif($alert->status === 'acknowledged')
                            <span class="badge badge-acknowledged">Dipantau</span>
                        @else
                            <span class="badge badge-active">Aktif</span>
                        @endif
                    </td>
                    <td>
                        @if($alert->alertable)
                            <div class="bold">{{ $alert->alertable->name }}</div>
                            <div style="font-size: 8px; color: #64748b;">
                                {{ class_basename($alert->alertable_type) === 'Student' ? 'Siswa' : 'Guru/Staf' }}
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $alert->created_at->format('d-m-Y') }}<br>
                        <span style="font-size: 8px; color: #64748b;">{{ $alert->created_at->format('H:i') }} WIB</span>
                    </td>
                    <td>
                        @if($alert->status === 'resolved')
                            <div style="color: #047857; font-weight: bold;">Terselesaikan</div>
                            <div style="font-size: 8px; color: #64748b;">Oleh: {{ $alert->resolvedBy->name ?? 'Sistem' }}</div>
                            <div style="font-size: 8px; color: #64748b;">Pada: {{ $alert->resolved_at ? $alert->resolved_at->format('d-m-Y H:i') : '-' }}</div>
                        @elseif($alert->escalatedTo)
                            <div style="color: #6d28d9; font-weight: bold;">Dieskalasikan</div>
                            <div style="font-size: 8px; color: #64748b;">Kepada: {{ $alert->escalatedTo->name }}</div>
                        @else
                            <span style="color: #64748b;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #64748b; font-weight: bold;">
                        Tidak ada data alert yang terdeteksi dalam arsip.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Halaman 1 dari 1 — Laporan Sistem Informasi Terpadu SISTA (SMA Islam Sultan Agung 1 Semarang)
    </div>

</body>
</html>
