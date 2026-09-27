<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Kelengkapan Kurikulum - {{ $activeYear->year ?? '' }}</title>
    <style>
        /* ============================================= */
        /* BASE STYLES — DomPDF Compatible               */
        /* ============================================= */
        @page {
            size: A4 portrait;
            margin: 2cm 1.5cm 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #1a1a1a;
            line-height: 1.5;
        }

        /* ============================================= */
        /* LETTERHEAD                                     */
        /* ============================================= */
        .letterhead-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .letterhead-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
            padding-right: 12px;
        }

        .logo-img {
            width: 65px;
            height: auto;
        }

        .school-info {
            text-align: center;
            padding-left: 8px;
        }

        .school-foundation {
            font-size: 10pt;
            color: #374151;
            letter-spacing: 0.5px;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #065f46;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .school-address {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 3px;
        }

        .school-contact {
            font-size: 8.5pt;
            color: #6b7280;
        }

        /* Divider Line */
        .header-divider {
            border: none;
            border-top: 3px double #065f46;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* ============================================= */
        /* DOCUMENT TITLE                                 */
        /* ============================================= */
        .doc-title {
            text-align: center;
            margin-bottom: 5px;
        }

        .doc-title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #065f46;
            margin-bottom: 3px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 11pt;
            color: #374151;
            margin-bottom: 20px;
        }

        /* ============================================= */
        /* DATA TABLES                                    */
        /* ============================================= */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #065f46;
            margin-top: 25px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #065f46;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }

        .data-table thead th {
            background-color: #065f46;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 8px 6px;
            border: 1px solid #065f46;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table tbody td {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f0fdf4;
        }

        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        /* Status Badges (inline) */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8.5pt;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .badge-approved {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .badge-draft {
            background-color: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde047;
        }

        .badge-revision {
            background-color: #fecaca;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Progress bar (simple) */
        .progress-bar-outer {
            width: 100%;
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 10px;
            border-radius: 5px;
        }

        .progress-green { background-color: #10b981; }
        .progress-amber { background-color: #f59e0b; }
        .progress-red { background-color: #ef4444; }

        /* ============================================= */
        /* SIGNATURE AREA                                 */
        /* ============================================= */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .sig-role {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .sig-line {
            margin-top: 70px;
            border-bottom: 1px solid #1a1a1a;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }

        .sig-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-top: 5px;
        }

        .sig-nip {
            font-size: 9pt;
            color: #6b7280;
        }

        /* ============================================= */
        /* FOOTER                                         */
        /* ============================================= */
        .print-date {
            font-size: 9pt;
            color: #9ca3af;
            text-align: right;
            margin-top: 30px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- ============================================= -->
    <!-- LETTERHEAD                                     -->
    <!-- ============================================= -->
    <table class="letterhead-table">
        <tr>
            <td class="logo-cell">
                {{-- School Logo placeholder - use actual path in production --}}
                <img src="{{ public_path('images/logo-sekolah.png') }}" class="logo-img" alt="Logo" onerror="this.style.display='none'">
            </td>
            <td class="school-info">
                <div class="school-foundation">YAYASAN BADAN PENDIDIKAN DAN SOSIAL SULTAN AGUNG</div>
                <div class="school-name">{{ mb_strtoupper(school_name()) }}</div>
                <div class="school-address">{{ school_address() }}</div>
                <div class="school-contact">{{ school_contact_line(['phone', 'email', 'website']) }}</div>
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    <!-- ============================================= -->
    <!-- DOCUMENT TITLE                                 -->
    <!-- ============================================= -->
    <div class="doc-title">
        <h1>Laporan Kelengkapan Kurikulum & Silabus Digital</h1>
    </div>
    <div class="doc-subtitle">
        Tahun Ajaran {{ $activeYear->year ?? '-' }}
    </div>

    <!-- ============================================= -->
    <!-- TABLE 1: REKAPITULASI SILABUS                  -->
    <!-- ============================================= -->
    <div class="section-title">Tabel 1 — Rekapitulasi Status Silabus per Mata Pelajaran</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th style="width: 60px;">Kode</th>
                <th>Mata Pelajaran</th>
                <th style="width: 90px;">Status</th>
                <th>Pengunggah</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($syllabiData as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-center">{{ $row['code'] ?? '-' }}</td>
                    <td class="text-left">{{ $row['name'] ?? '-' }}</td>
                    <td class="text-center">
                        @if(($row['status'] ?? '') === 'approved')
                            <span class="badge badge-approved">Disetujui</span>
                        @elseif(($row['status'] ?? '') === 'draft')
                            <span class="badge badge-draft">Draft</span>
                        @elseif(($row['status'] ?? '') === 'revision_needed')
                            <span class="badge badge-revision">Revisi</span>
                        @else
                            <span class="badge badge-draft">{{ ucfirst($row['status'] ?? '-') }}</span>
                        @endif
                    </td>
                    <td class="text-left">{{ $row['uploader'] ?? '-' }}</td>
                    <td class="text-left" style="font-size: 9pt; color: #6b7280;">{{ $row['notes'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #9ca3af;">Belum ada data silabus.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ============================================= -->
    <!-- TABLE 2: REKAPITULASI RPP PER GURU             -->
    <!-- ============================================= -->
    <div class="section-title">Tabel 2 — Rekapitulasi Kelengkapan RPP per Guru</div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Guru</th>
                <th style="width: 70px;">Total RPP</th>
                <th style="width: 75px;">Disetujui</th>
                <th style="width: 80px;">Rate (%)</th>
                <th style="width: 120px;">Progress</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rppData as $i => $row)
                @php
                    $rate = $row['rate'] ?? 0;
                    $progressClass = $rate >= 80 ? 'progress-green' : ($rate >= 50 ? 'progress-amber' : 'progress-red');
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td class="text-left">{{ $row['name'] ?? '-' }}</td>
                    <td class="text-center">{{ $row['total'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['approved'] ?? 0 }}</td>
                    <td class="text-center" style="font-weight: bold; color: {{ $rate >= 80 ? '#065f46' : ($rate >= 50 ? '#92400e' : '#991b1b') }};">
                        {{ $rate }}%
                    </td>
                    <td>
                        <table style="width: 100%; border: none; border-collapse: collapse;">
                            <tr>
                                <td style="border: none; padding: 0;">
                                    <div class="progress-bar-outer">
                                        <div class="progress-bar-inner {{ $progressClass }}" style="width: {{ min($rate, 100) }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #9ca3af;">Belum ada data RPP guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- ============================================= -->
    <!-- SIGNATURE AREA                                 -->
    <!-- ============================================= -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-role">Kepala Sekolah</div>
                <div class="sig-line"></div>
                <div class="sig-name">____________________</div>
                <div class="sig-nip">NIP. ____________________</div>
            </td>
            <td>
                <div class="sig-role">Waka Kurikulum</div>
                <div class="sig-line"></div>
                <div class="sig-name">____________________</div>
                <div class="sig-nip">NIP. ____________________</div>
            </td>
        </tr>
    </table>

    <!-- ============================================= -->
    <!-- PRINT DATE                                     -->
    <!-- ============================================= -->
    <div class="print-date">
        Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y — HH:mm') }} WIB
    </div>

</body>
</html>
