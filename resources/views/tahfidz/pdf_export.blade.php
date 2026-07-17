<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Tahfidz Al-Qur'an - {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #0f5132;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header-logo {
            width: 70px;
            text-align: center;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text h1 {
            font-size: 15px;
            margin: 0;
            color: #0f5132;
            text-transform: uppercase;
            font-weight: 900;
            letter-spacing: 1px;
        }
        .header-text h2 {
            font-size: 13px;
            margin: 3px 0 0 0;
            color: #198754;
            font-weight: 700;
        }
        .header-text p {
            font-size: 9px;
            margin: 3px 0 0 0;
            color: #6c757d;
        }
        .report-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            color: #212529;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
            color: #495057;
        }
        .info-colon {
            width: 15px;
            color: #495057;
        }
        .info-value {
            color: #212529;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 25px;
        }
        .summary-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            color: #0f5132;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-grid td {
            width: 25%;
            padding: 5px;
            text-align: center;
            border-right: 1px solid #dee2e6;
        }
        .summary-grid td:last-child {
            border-right: none;
        }
        .summary-value {
            font-size: 13px;
            font-weight: bold;
            color: #212529;
        }
        .summary-label {
            font-size: 8px;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .progress-bar-container {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 4px;
            height: 10px;
            margin-top: 5px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background-color: #198754;
            height: 100%;
            border-radius: 4px;
        }
        .logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .logs-table th {
            background-color: #0f5132;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border: 1px solid #0f5132;
            text-align: left;
        }
        .logs-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }
        .logs-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .type-badge {
            display: inline-block;
            padding: 1px 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
            background-color: #e2f0d9;
            color: #385723;
        }
        .type-badge.murajaah {
            background-color: #e2f0f9;
            color: #1f4e78;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.3%;
            text-align: center;
            vertical-align: top;
            padding-top: 10px;
        }
        .sig-title {
            font-weight: bold;
            margin-bottom: 50px;
            color: #495057;
        }
        .sig-name {
            font-weight: bold;
            text-decoration: underline;
            color: #212529;
        }
        .sig-nip {
            font-size: 9px;
            color: #6c757d;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <!-- Fallback to text box if image doesn't exist, styled to match -->
                <div style="width: 55px; height: 55px; background-color: #0f5132; color: white; border-radius: 50%; display: inline-block; line-height: 55px; font-weight: bold; font-size: 10px;">SA-1</div>
            </td>
            <td class="header-text">
                <h1>Yayasan Badan Wakaf Sultan Agung</h1>
                <h2>SMA Islam Sultan Agung 1 Semarang</h2>
                <p>Jalan Mataram No. 657, Wonodri, Semarang 50242 | Telp. (024) 6580019</p>
                <p>Website: www.smaislasa1.sch.id | E-mail: info@smaislasa1.sch.id</p>
            </td>
        </tr>
    </table>

    <div class="report-title">
        Laporan Perkembangan Tahfidz Al-Qur'an
    </div>

    <!-- Student Details -->
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Siswa</td>
            <td class="info-colon">:</td>
            <td class="info-value"><strong>{{ $student->name }}</strong></td>
            
            <td class="info-label" style="padding-left: 30px;">Tahun Ajaran</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $academicYear ? $academicYear->year : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Nomor Induk Siswa</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $student->nis }}</td>

            <td class="info-label" style="padding-left: 30px;">Semester</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $academicYear ? $academicYear->semester : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Kelas</td>
            <td class="info-colon">:</td>
            <td class="info-value">Kelas {{ $student->classroom?->name ?? 'Belum Diatur' }}</td>

            <td class="info-label" style="padding-left: 30px;">Poin Keaktifan</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $student->total_xp }} XP (Level {{ $student->level }})</td>
        </tr>
    </table>

    <!-- Target & Capaian Box -->
    <div class="summary-box">
        <div class="summary-title">Pencapaian Target Hafalan Semester</div>
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-value">
                        {{ $progress['target'] ? $progress['target']->target_juz : 0 }} Juz
                    </div>
                    <div class="summary-label">Target Juz</div>
                </td>
                <td>
                    <div class="summary-value">
                        {{ $progress['target'] ? $progress['target']->achieved_juz : 0 }} Juz
                    </div>
                    <div class="summary-label">Tercapai (Juz)</div>
                </td>
                <td>
                    <div class="summary-value">
                        {{ $progress['target'] ? $progress['target']->target_pages : 0 }} Hal
                    </div>
                    <div class="summary-label">Target Halaman</div>
                </td>
                <td>
                    <div class="summary-value">
                        {{ $progress['target'] ? $progress['target']->achieved_pages : 0 }} Hal
                    </div>
                    <div class="summary-label">Tercapai (Halaman)</div>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 12px; border-top: 1px solid #dee2e6; padding-top: 8px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="font-weight: bold; font-size: 9px; color: #495057;">Persentase Ketercapaian Target:</td>
                    <td style="text-align: right; font-weight: bold; color: #0f5132;">{{ $progress['progress_percent'] }}%</td>
                </tr>
            </table>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: {{ $progress['progress_percent'] }}%"></div>
            </div>
            
            @if($progress['target'] && $progress['target']->notes)
                <p style="margin: 8px 0 0 0; font-size: 9px; color: #6c757d; font-style: italic;">
                    <strong>Evaluasi Pembimbing:</strong> "{{ $progress['target']->notes }}"
                </p>
            @endif
        </div>
    </div>

    <!-- Logs Table -->
    <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px; color: #0f5132; text-transform: uppercase;">
        Rincian Jurnal Setoran Hafalan
    </div>
    <table class="logs-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Tipe</th>
                <th style="width: 20%;">Surah</th>
                <th style="width: 12%;">Ayat</th>
                <th style="width: 8%; text-align: center;">Juz</th>
                <th style="width: 8%; text-align: center;">Halaman</th>
                <th style="width: 8%; text-align: center;">Grade</th>
                <th style="width: 17%;">Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse($progress['logs'] as $idx => $log)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $log->date?->format('d/m/Y') }}</td>
                    <td>
                        <span class="type-badge {{ $log->type }}">
                            {{ $log->type }}
                        </span>
                    </td>
                    <td><strong>{{ $log->surah_name }}</strong></td>
                    <td>Ayat {{ $log->from_ayat }} - {{ $log->to_ayat }}</td>
                    <td style="text-align: center;">Juz {{ $log->juz ?? '-' }}</td>
                    <td style="text-align: center;">{{ $log->pages_count }}</td>
                    <td style="text-align: center; font-weight: bold; color: #0f5132;">{{ $log->grade ?? '-' }}</td>
                    <td style="font-style: italic; color: #6c757d;">{{ $log->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #6c757d;">Belum ada setoran hafalan pada semester ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-title">Orang Tua / Wali Siswa,</div>
                <div style="height: 50px;"></div>
                <div class="sig-name">( ............................................. )</div>
            </td>
            <td>
                <div class="sig-title">Ustadz/Ustadzah Pembimbing,</div>
                <div style="height: 50px;"></div>
                <div class="sig-name">{{ $progress['logs']->first()?->teacher?->name ?? 'Pembimbing Tahfidz' }}</div>
                <div class="sig-nip">NIP. .....................................</div>
            </td>
            <td>
                <div class="sig-title">Mengetahui, Kepala Sekolah</div>
                <div style="height: 50px;"></div>
                <div class="sig-name">Drs. H. Soleh, M.Pd</div>
                <div class="sig-nip">NIP. 19680312 199403 1 005</div>
            </td>
        </tr>
    </table>

</body>
</html>
