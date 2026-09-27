<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Analisis Butir Soal - {{ $exam->title }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px;
            color: #333333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #0d5c3a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .school-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0d5c3a;
            margin: 0;
        }
        .school-subtitle {
            font-size: 7px;
            font-weight: bold;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            margin: 3px 0 8px 0;
            color: #0f172a;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 3px 6px;
            font-size: 9px;
            border: none;
            text-align: left;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            color: #475569;
            width: 150px;
        }
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 12px;
        }
        .summary-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 8px;
        }
        table.analysis-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            table-layout: fixed;
        }
        table.analysis-table th {
            background-color: #0d5c3a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 5px;
            border: 1px solid #0d5c3a;
            text-align: center;
        }
        table.analysis-table td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            font-size: 8px;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .status-keep {
            color: #15803d;
            font-weight: bold;
        }
        .status-revise {
            color: #b45309;
            font-weight: bold;
        }
        .status-discard {
            color: #be123c;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: right;
            font-size: 6px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 2px;
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
    <div class="doc-title">Laporan Analisis Butir Soal Ujian Online CBT</div>

    <!-- Meta Info -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Judul Ujian</td>
            <td>: {{ $exam->title }}</td>
            <td class="meta-label">Total Peserta</td>
            <td>: {{ $analysis['stats']['total_participants'] }} siswa</td>
        </tr>
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td>: {{ $exam->subject?->name ?? '-' }}</td>
            <td class="meta-label">Rata-rata Nilai</td>
            <td>: {{ $analysis['stats']['average'] }} (Std Deviasi: {{ $analysis['stats']['std_dev'] }})</td>
        </tr>
        <tr>
            <td class="meta-label">Kelas</td>
            <td>: {{ $exam->classroom?->name ?? 'Semua Kelas' }}</td>
            <td class="meta-label font-bold">Reliabilitas (KR-20)</td>
            <td class="text-bold">: {{ $analysis['stats']['reliability'] }} (Kategori: {{ $analysis['stats']['reliability_text'] }})</td>
        </tr>
    </table>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-title">Ringkasan Analisis Kualitas Soal</div>
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; padding: 0;">
                    <strong>Tingkat Kesukaran:</strong> 
                    Mudah: {{ $analysis['difficulty_counts']['Mudah'] }} soal | 
                    Sedang: {{ $analysis['difficulty_counts']['Sedang'] }} soal | 
                    Sulit: {{ $analysis['difficulty_counts']['Sulit'] }} soal
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <strong>Rekomendasi Tindak Lanjut:</strong> 
                    Pertahankan: {{ $analysis['recommendation_counts']['Pertahankan'] }} soal | 
                    Revisi: {{ $analysis['recommendation_counts']['Revisi'] }} soal | 
                    Buang: {{ $analysis['recommendation_counts']['Buang'] }} soal
                </td>
            </tr>
        </table>
    </div>

    <!-- Main Analysis Table -->
    <table class="analysis-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Pertanyaan Soal</th>
                <th style="width: 8%;">Kesukaran (P)</th>
                <th style="width: 8%;">Kesimpulan P</th>
                <th style="width: 8%;">Pembeda (D)</th>
                <th style="width: 8%;">Kesimpulan D</th>
                <th style="width: 10%;">Rekomendasi</th>
                <th style="width: 18%;">Catatan Perbaikan / Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($analysis['questions'] as $i => $item)
                @php
                    $q = $item['question'];
                    $diff = $item['difficulty'];
                    $disc = $item['discrimination'];
                    $rec = $item['recommendation'];

                    $recStyle = match($rec['recommendation']) {
                        'Pertahankan' => 'status-keep',
                        'Revisi' => 'status-revise',
                        default => 'status-discard'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $q->pivot->order ?? ($i + 1) }}</td>
                    <td>{{ $q->question_text }}</td>
                    <td class="text-center">{{ $diff['difficulty_index'] }}</td>
                    <td class="text-center">{{ $diff['difficulty_text'] }}</td>
                    <td class="text-center">{{ $disc['discrimination_index'] }}</td>
                    <td class="text-center">{{ $disc['discrimination_text'] }}</td>
                    <td class="text-center {{ $recStyle }}">{{ $rec['recommendation'] }}</td>
                    <td>{{ $rec['notes'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signatures -->
    <table style="width: 100%; border: none; margin-top: 25px;">
        <tr>
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center; font-size: 8px;">
                Semarang, {{ now()->format('d M Y') }}<br>
                Guru Pengampu Mata Pelajaran,<br><br><br><br><br>
                <strong>{{ $exam->creator?->name ?? 'Staf Pengajar' }}</strong>
            </td>
        </tr>
    </table>

    <!-- Footer PDF -->
    <div class="footer">
        {{ school_name() }} — Laporan Analisis Butir Soal Resmi SISTA
    </div>

</body>
</html>
