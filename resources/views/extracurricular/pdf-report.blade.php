<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Semester Ekskul - {{ $extracurricular->name }}</title>
    <style>
        body {
            font-family: Georgia, 'Times New Roman', serif;
            color: #000;
            line-height: 1.5;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 4px double #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 20pt;
            text-transform: uppercase;
            margin: 0;
            font-weight: bold;
        }
        .header h2 {
            font-size: 14pt;
            text-transform: uppercase;
            margin: 5px 0 0 0;
            font-weight: normal;
        }
        .header p {
            font-size: 9pt;
            font-style: italic;
            margin: 5px 0 0 0;
        }
        .title {
            text-align: center;
            margin-bottom: 30px;
        }
        .title h3 {
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .info-table td {
            padding: 4px 0;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 25px;
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10pt;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Sekolah -->
    <div class="header">
        <h1>SMA Islam Sultan Agung 1</h1>
        <h2>Yayasan Badan Wakaf Sultan Agung</h2>
        <p>Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="title">
        <h3>Laporan Semester Kegiatan Ekstrakurikuler</h3>
    </div>

    <!-- Profil Ekskul -->
    <table class="info-table">
        <tr>
            <td style="width: 25%; font-weight: bold;">Nama Ekstrakurikuler</td>
            <td style="width: 5%;">:</td>
            <td style="width: 70%;">{{ $extracurricular->name }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Guru Pembina (Advisor)</td>
            <td>:</td>
            <td>{{ $extracurricular->advisor->name ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Hari & Waktu Latihan</td>
            <td>:</td>
            <td>{{ $extracurricular->schedule_day }}, {{ $extracurricular->schedule_time }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Tempat Latihan</td>
            <td>:</td>
            <td>{{ $extracurricular->location }}</td>
        </tr>
    </table>

    <!-- 1. Daftar Anggota & Nilai -->
    <div class="section-title">1. Daftar Anggota Aktif & Penilaian Rapor</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 35%;">Nama Anggota</th>
                <th style="width: 20%;">NISN</th>
                <th style="width: 15%; text-align: center;">Predikat</th>
                <th style="width: 25%;">Keterangan / Capaian</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($extracurricular->activeMembers as $mb)
                @php
                    $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                    $gr = \App\Models\ExtracurricularGrade::where('extracurricular_member_id', $mb->id)
                        ->where('academic_year_id', $activeYear->id ?? 0)
                        ->first();
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="font-weight: bold;">{{ $mb->student->name }}</td>
                    <td>{{ $mb->student->nisn ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $gr->grade ?? '-' }}</td>
                    <td>{{ $gr->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Belum ada anggota terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 2. Laporan Lomba & Prestasi -->
    <div class="section-title">2. Laporan Prestasi & Kejuaraan Lomba</div>
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 40%;">Nama Penghargaan / Lomba</th>
                <th style="width: 20%;">Tingkat</th>
                <th style="width: 20%;">Tanggal</th>
                <th style="width: 15%;">Penyelenggara</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($extracurricular->achievements as $ac)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="font-weight: bold;">{{ $ac->title }}</td>
                    <td>{{ strtoupper($ac->level) }}</td>
                    <td>{{ $ac->event_date->format('d M Y') }}</td>
                    <td>{{ $ac->organizer }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">Belum ada prestasi kesiswaan tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Semarang, {{ now()->translatedFormat('d F Y') }}</p>
        <p style="margin-top: 5px; margin-bottom: 60px;">Pembina Ekstrakurikuler,</p>
        <p style="font-weight: bold; text-decoration: underline;">{{ $extracurricular->advisor->name ?? 'Pembina Ekskul' }}</p>
    </div>

</body>
</html>
