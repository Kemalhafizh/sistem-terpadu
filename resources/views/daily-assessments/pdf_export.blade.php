<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Nilai - {{ $assessment->title }}</title>
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
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
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
        <h1 class="school-name">{{ school_name() }}</h1>
        <p class="school-subtitle">Sistem Informasi Sekolah Terpadu SISTA</p>
    </div>

    <!-- Doc Title -->
    <div class="doc-title">Laporan Hasil Penilaian Formatif Harian</div>

    <!-- Meta Information -->
    <table class="meta-table">
        <tr>
            <td width="15%" class="font-bold">Penilaian:</td>
            <td width="35%">{{ $assessment->title }}</td>
            <td width="15%" class="font-bold">Kelas:</td>
            <td width="35%">{{ $assessment->classroom->name }}</td>
        </tr>
        <tr>
            <td class="font-bold">Mata Pelajaran:</td>
            <td>{{ $assessment->subject->name }}</td>
            <td class="font-bold">Tanggal Ujian:</td>
            <td>{{ $assessment->assessment_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tipe:</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $assessment->type) }}</td>
            <td class="font-bold">Batas KKM:</td>
            <td>{{ number_format($assessment->effective_kkm, 0) }}</td>
        </tr>
        <tr>
            <td class="font-bold">Tahun Akademik:</td>
            <td>{{ $assessment->academicYear->year }} ({{ $assessment->academicYear->semester }})</td>
            <td class="font-bold">Guru Pengampu:</td>
            <td>{{ $assessment->teacher->name }}</td>
        </tr>
    </table>

    <!-- Scores Table -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 20%;">NIS</th>
                <th>Nama Siswa</th>
                <th class="text-center" style="width: 12%;">Nilai Awal</th>
                <th class="text-center" style="width: 15%;">Status</th>
                <th class="text-center" style="width: 15%;">Nilai Remedial</th>
                <th class="text-center" style="width: 12%;">Nilai Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assessment->scores as $index => $score)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $score->student->nis }}</td>
                    <td class="font-bold">{{ $score->student->name }}</td>
                    <td class="text-center">{{ $score->score !== null ? number_format($score->score, 0) : '-' }}</td>
                    <td class="text-center" style="font-size: 8px;">
                        @if($score->remedial_status === 'not_needed')
                            Lulus Awal
                        @elseif($score->remedial_status === 'pending')
                            Pending Remedial
                        @elseif($score->remedial_status === 'completed')
                            Remedial Selesai
                        @endif
                    </td>
                    <td class="text-center">{{ $score->remedial_score !== null ? number_format($score->remedial_score, 0) : '-' }}</td>
                    <td class="text-center font-bold">{{ $score->final_score !== null ? number_format($score->final_score, 0) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Sign-off Area -->
    <table class="sign-area">
        <tr>
            <td></td>
            <td class="text-center">
                Semarang, {{ now()->format('d F Y') }}<br>
                Guru Mata Pelajaran,<br><br><br><br>
                <span class="font-bold" style="text-decoration: underline;">{{ $assessment->teacher->name }}</span>
            </td>
        </tr>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        {{ school_name() }} — Dokumen Laporan Resmi SISTA
    </div>

</body>
</html>
