<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kesiapan Akreditasi Sekolah</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #1f2937;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo-cell {
            width: 70px;
            text-align: center;
            vertical-align: middle;
        }
        .logo-placeholder {
            width: 60px;
            height: 60px;
            background-color: #10b981;
            color: #ffffff;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            font-size: 24px;
            font-weight: bold;
            display: inline-block;
        }
        .title-cell {
            text-align: center;
            vertical-align: middle;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .school-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin: 2px 0 0 0;
        }
        .school-address {
            font-size: 8px;
            color: #666666;
            margin: 4px 0 0 0;
        }
        .report-title-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .report-subtitle {
            font-size: 9px;
            color: #4b5563;
            margin-top: 4px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
        }
        .meta-label {
            font-weight: bold;
            color: #4b5563;
            width: 25%;
        }
        .meta-value {
            color: #111827;
        }
        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 4px;
            margin-top: 25px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px;
            border: 1px solid #0f766e;
            text-align: left;
        }
        .data-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 9px;
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
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .page-break {
            page-break-before: always;
        }
        .signature-table {
            width: 100%;
            margin-top: 50px;
        }
        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-pad {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-title {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Letterhead -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo-cell">
                <div class="logo-cell" style="font-size: 28px; line-height: 1;">🕌</div>
            </td>
            <td class="title-cell">
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-subtitle">SMA Islam Sultan Agung 1 Semarang</div>
                <div class="school-address">{{ $schoolAddress }}</div>
            </td>
        </tr>
    </table>

    <!-- Title -->
    <div class="report-title-container">
        <h2 class="report-title">Laporan Kepatuhan & Kesiapan Akreditasi</h2>
        <div class="report-subtitle">Tahun Ajaran: {{ $activeYear ? $activeYear->year . ' (' . ucfirst($activeYear->semester) . ')' : '-' }}</div>
    </div>

    <!-- Metadata Table -->
    <table class="meta-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="meta-label">Tanggal Cetak</td>
            <td class="meta-value">{{ now()->format('d F Y H:i') }}</td>
            <td class="meta-label">Skor Akhir Kesiapan</td>
            <td class="meta-value" style="font-weight: bold; font-size: 13px; color: #0f766e;">{{ number_format($report['overall_score'], 2) }}%</td>
        </tr>
        <tr>
            <td class="meta-label">Status Kelayakan</td>
            <td class="meta-value" colspan="3" style="font-weight: bold; text-transform: uppercase;">
                @php
                    $score = $report['overall_score'];
                    if ($score >= 91) {
                        echo "AKREDITASI A (UNGGUL) - LAYAK AUDIT EKSTERNAL";
                    } elseif ($score >= 81) {
                        echo "AKREDITASI B (BAIK) - LAYAK VISITASI";
                    } elseif ($score >= 71) {
                        echo "AKREDITASI C (CUKUP) - PERLU PENINGKATAN MINIMAL";
                    } else {
                        echo "BELUM LAYAK VISITASI - SEGARA PERBAIKI INDIKATOR KUNCI";
                    }
                @endphp
            </td>
        </tr>
    </table>

    <!-- Section 1: Indikator Evaluasi Kepatuhan -->
    <div class="section-header">1. Rekapitulasi Pemenuhan 10 Indikator Kepatuhan</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Parameter Indikator</th>
                <th style="width: 15%; text-align: center;">Nilai Riil</th>
                <th style="width: 15%; text-align: center;">Skor Pemenuhan</th>
                <th style="width: 15%; text-align: center;">Status</th>
                <th style="width: 20%;">Catatan Instrumen</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($report['metrics'] as $key => $metric)
            @php
                $badge = 'badge-danger';
                if ($metric['status'] === 'terpenuhi') {
                    $badge = 'badge-success';
                } elseif ($metric['status'] === 'perlu_perbaikan') {
                    $badge = 'badge-warning';
                }
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td style="font-weight: bold;">{{ $metric['label'] }}</td>
                <td class="text-center">{{ $metric['value'] }}</td>
                <td class="text-center">{{ number_format($metric['score'], 0) }}%</td>
                <td class="text-center">
                    <span class="badge {{ $badge }}">{{ str_replace('_', ' ', $metric['status']) }}</span>
                </td>
                <td style="font-size: 8px; color: #666666;">{{ $metric['desc'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Letterhead for Page 2 -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo-cell">
                <div class="logo-cell" style="font-size: 28px; line-height: 1;">🕌</div>
            </td>
            <td class="title-cell">
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-subtitle">SMA Islam Sultan Agung 1 Semarang</div>
            </td>
        </tr>
    </table>

    <!-- Section 2: Daftar Berkas Bukti Fisik Terunggah -->
    <div class="section-header">2. Arsip Berkas Bukti Fisik Akreditasi Terunggah</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Standar SNP</th>
                <th style="width: 40%;">Nama Dokumen Bukti Fisik</th>
                <th style="width: 20%;">Pengunggah</th>
                <th style="width: 20%;">Tanggal Unggah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $index => $doc)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="font-weight: bold;">{{ $doc->standard->code }}</td>
                <td>{{ $doc->name }}</td>
                <td>{{ $doc->uploader->name }}</td>
                <td>{{ $doc->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #666666; font-style: italic; padding: 15px;">
                    Belum ada bukti fisik terunggah di repositori digital.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section 3: Timeline Visitasi / Milestones -->
    <div class="section-header">3. Timeline & Agenda Visitasi Akreditasi</div>
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Tugas / Agenda Persiapan</th>
                <th style="width: 25%; text-align: center;">Target Tanggal</th>
                <th style="width: 30%; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($milestones as $index => $m)
            @php
                $isComp = $m->status === 'completed';
                $badge = $isComp ? 'badge-success' : 'badge-warning';
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: bold;">{{ $m->title }}</div>
                    @if($m->description)
                        <div style="font-size: 8px; color: #666666; margin-top: 2px;">{{ $m->description }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $m->due_date->format('d M Y') }}</td>
                <td class="text-center">
                    <span class="badge {{ $badge }}">{{ $m->status }}</span>
                    @if($m->completed_at)
                        <div style="font-size: 7px; color: #666666; margin-top: 2px;">Selesai: {{ $m->completed_at->format('d/m/Y') }}</div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="color: #666666; font-style: italic; padding: 15px;">
                    Belum ada milestones persiapan visitasi yang didefinisikan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature Pad -->
    <table class="signature-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="signature-cell">
                <div>Mengetahui,</div>
                <div class="signature-title">Waka Bidang Kurikulum</div>
                <div class="signature-pad">Drs. H. Mulyono, M.Pd</div>
                <div class="signature-title">NIP. 19740512 200312 1 002</div>
            </td>
            <td class="signature-cell">
                <div>Semarang, {{ now()->format('d F Y') }}</div>
                <div class="signature-title">Kepala Sekolah</div>
                <div class="signature-pad">Dra. Hj. Wahyu Ningsih, M.Pd</div>
                <div class="signature-title">NIP. 19710825 199803 2 001</div>
            </td>
        </tr>
    </table>

</body>
</html>
