<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Evaluasi Diri Sekolah (EDS) - {{ $period->name }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #111;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9px;
            color: #555;
        }
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-section h3 {
            margin: 0;
            font-size: 13px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border: 0;
            background-color: #fcfcfc;
            border: 1px solid #eee;
            padding: 8px;
            border-radius: 4px;
        }
        .meta-table td {
            padding: 4px 8px;
            border: 0;
        }
        .summary-box {
            background-color: #f0f4f8;
            border-left: 4px solid #1a56db;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 0 4px 4px 0;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box td {
            padding: 4px;
        }
        .summary-title {
            font-weight: bold;
            color: #1a56db;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #111;
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .main-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-weight: bold;
            text-align: left;
            font-size: 10px;
            color: #374151;
        }
        .main-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
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
            font-size: 8px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-draft { background-color: #e5e7eb; color: #4b5563; }
        .badge-progress { background-color: #e0e7ff; color: #4338ca; }
        .badge-completed { background-color: #d1fae5; color: #065f46; }
        
        .score-box {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .score-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            color: #fff;
        }
        .score-1 { background-color: #ef4444; }
        .score-2 { background-color: #f59e0b; }
        .score-3 { background-color: #3b82f6; }
        .score-4 { background-color: #10b981; }

        .gap-indicator {
            background-color: #fef2f2;
        }
        .signature-section {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            border: 0;
        }
        .signature-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }
        .signature-box {
            text-align: center;
            width: 220px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    <div class="header">
        <h2>{{ $schoolName }}</h2>
        <p>{{ school_contact_line() }}</p>
    </div>

    <!-- Judul Laporan -->
    <div class="title-section">
        <h3>Laporan Hasil Evaluasi Diri Sekolah (EDS)</h3>
        <p style="margin: 4px 0 0 0; font-size: 11px;">Tingkat Ketercapaian Standar Nasional Pendidikan (SNP)</p>
    </div>

    <!-- Tabel Meta Data -->
    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Nama Periode:</strong></td>
            <td width="32%">{{ $period->name }}</td>
            <td width="18%"><strong>Tanggal Mulai:</strong></td>
            <td width="32%">{{ $period->start_date ? \Carbon\Carbon::parse($period->start_date)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Tahun Akademik:</strong></td>
            <td>{{ $academicYear->year }} ({{ ucfirst($academicYear->semester) }})</td>
            <td><strong>Tanggal Selesai:</strong></td>
            <td>{{ $period->end_date ? \Carbon\Carbon::parse($period->end_date)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <td><strong>Status Evaluasi:</strong></td>
            <td>
                @if($period->status === 'draft')
                    <span class="badge badge-draft">Draft</span>
                @elseif($period->status === 'in_progress')
                    <span class="badge badge-progress">Dalam Proses</span>
                @else
                    <span class="badge badge-completed">Selesai</span>
                @endif
            </td>
            <td><strong>Tanggal Cetak:</strong></td>
            <td>{{ $generatedAt }} WIB</td>
        </tr>
    </table>

    <!-- Ringkasan Mutu Agregat -->
    <div class="summary-box">
        <div class="summary-title">Ringkasan Mutu Sekolah</div>
        <table>
            <tr>
                <td width="30%"><strong>Skor Mutu Agregat (Skala 1-4):</strong></td>
                <td width="70%"><span class="score-box">{{ number_format($scoresData['overall_average'], 2) }}</span> / 4.00</td>
            </tr>
            <tr>
                <td><strong>Progres Pengisian Evaluasi:</strong></td>
                <td><strong>{{ $scoresData['overall_progress'] }}%</strong> ({{ $scoresData['assessed_indicators'] }} dari {{ $scoresData['total_indicators'] }} Indikator Dinilai)</td>
            </tr>
            <tr>
                <td><strong>Predikat Ketercapaian:</strong></td>
                <td>
                    @if($scoresData['overall_average'] >= 3.5)
                        <strong style="color: #10b981;">UNGGUL (Melampaui kriteria standar secara konsisten)</strong>
                    @elseif($scoresData['overall_average'] >= 3.0)
                        <strong style="color: #3b82f6;">BAIK (Memenuhi seluruh kriteria standar dengan baik)</strong>
                    @elseif($scoresData['overall_average'] >= 2.0)
                        <strong style="color: #f59e0b;">CUKUP (Memenuhi standar minimum dengan catatan perbaikan)</strong>
                    @else
                        <strong style="color: #ef4444;">KURANG (Belum memenuhi standar minimum)</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Nilai Rata-rata per 8 SNP -->
    <div class="section-title">Nilai Pencapaian 8 Standar Nasional Pendidikan (SNP)</div>
    <table class="main-table">
        <thead>
            <tr>
                <th width="8%">Kode</th>
                <th width="42%">Standar Nasional Pendidikan (SNP)</th>
                <th width="15%" class="text-center">Indikator Dinilai</th>
                <th width="15%" class="text-center">Progres</th>
                <th width="20%" class="text-center">Rata-rata Skor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($scoresData['standards'] as $code => $data)
            <tr>
                <td><strong>{{ $code }}</strong></td>
                <td>
                    <strong>{{ $data['standard']->name }}</strong>
                    <div style="font-size: 8px; color: #666; margin-top: 2px;">{{ $data['standard']->description }}</div>
                </td>
                <td class="text-center">{{ $data['assessed_count'] }} / {{ $data['total_count'] }}</td>
                <td class="text-center">{{ $data['progress_percentage'] }}%</td>
                <td class="text-center">
                    @php
                        $roundedScore = round($data['average']);
                        $scoreClass = 'score-1';
                        if ($roundedScore >= 4) $scoreClass = 'score-4';
                        elseif ($roundedScore >= 3) $scoreClass = 'score-3';
                        elseif ($roundedScore >= 2) $scoreClass = 'score-2';
                    @endphp
                    <span class="score-pill {{ $scoreClass }}">{{ number_format($data['average'], 2) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Daftar Indikator dengan Skor Rendah (Gap Analysis) -->
    <div class="section-title" style="margin-top: 0;">Analisis Kesenjangan (Rekomendasi Prioritas Perbaikan - Skor < 3.00)</div>
    <p style="margin-bottom: 10px; color: #555; font-size: 9px;">Berikut adalah daftar indikator yang belum memenuhi target mutu (skor kurang dari 3.00 atau belum dievaluasi) dan memerlukan prioritas tindakan perbaikan segera.</p>
    
    @if(count($gapData) > 0)
    <table class="main-table">
        <thead>
            <tr>
                <th width="10%">Standar</th>
                <th width="25%">Indikator / Kode</th>
                <th width="8%" class="text-center">Skor</th>
                <th width="27%">Catatan Bukti / Kendala</th>
                <th width="30%">Rekomendasi Aksi Perbaikan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($gapData as $gap)
            <tr class="gap-indicator">
                <td><strong>{{ $gap['standard']->code }}</strong></td>
                <td>
                    <strong>{{ $gap['indicator']->code }}</strong> - {{ $gap['indicator']->name }}
                    <div style="font-size: 8px; color: #777; margin-top: 2px;">{{ $gap['indicator']->description }}</div>
                </td>
                <td class="text-center">
                    @if($gap['is_assessed'])
                        <span class="score-pill score-{{ $gap['score'] }}">{{ $gap['score'] }}</span>
                    @else
                        <span class="badge badge-draft" style="font-size: 7px;">Belum Dinilai</span>
                    @endif
                </td>
                <td>{{ $gap['evidence'] }}</td>
                <td><strong>{{ $gap['recommendation'] }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding: 15px; border: 1px solid #d1fae5; background-color: #ecfdf5; border-radius: 4px; color: #065f46; margin-bottom: 20px; font-size: 10px;">
        <strong>Luar Biasa!</strong> Tidak ditemukan kesenjangan mutu. Seluruh indikator telah dinilai dan memenuhi standar minimal dengan nilai Baik atau Unggul (Skor &ge; 3.00).
    </div>
    @endif

    <!-- Tanda Tangan Pengesahan -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td></td>
                <td width="300px">
                    <div class="signature-box" style="float: right;">
                        <p>Semarang, {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
                        <p>Kepala Sekolah,</p>
                        <br><br><br><br>
                                                <p><strong>{{ signature_name(school_principal_name()) }}</strong></p>
                        <p>{{ signature_nip(school_principal_nip()) }}</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
