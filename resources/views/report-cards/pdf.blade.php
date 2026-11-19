<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak Rapor - {{ $student->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 12px;
        }
        .student-info {
            width: 100%;
            margin-bottom: 20px;
        }
        .student-info td {
            padding: 3px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 6px;
        }
        .table-data th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .footer-section {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            width: 30%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 70px;
        }
        .notes-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 50px;
            margin-bottom: 20px;
        }
        .title-notes {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .preview-banner {
            border: 2px solid #991b1b;
            background-color: #fef2f2;
            color: #991b1b;
            text-align: center;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 11px;
            letter-spacing: 1px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @if($isPreview ?? true)
        <div class="preview-banner">PRATINJAU - BUKAN RAPOR RESMI (Dokumen resmi diterbitkan melalui e-Rapor Kemendikdasmen)</div>
    @endif

    <div class="header">
        <p>{{ school_foundation_name(false) }}</p>
        <h1>{{ ($reportType ?? 'pas') === 'pts' ? 'LAPORAN HASIL BELAJAR TENGAH SEMESTER (PTS)' : 'LAPORAN HASIL BELAJAR SISWA' }}</h1>
        <h1>{{ mb_strtoupper(school_name()) }}</h1>
        <p>{{ school_address() }}</p>
    </div>

    <table class="student-info">
        <tr>
            <td width="16%"><strong>Nama Siswa</strong></td>
            <td width="2%">:</td>
            <td width="40%">{{ $student->name }}</td>
            <td width="17%"><strong>Kelas / Fase</strong></td>
            <td width="2%">:</td>
            <td width="23%">{{ $student->classroom ? $student->classroom->name : '-' }} / Fase {{ $phase ?? ($student->classroom?->grade_level === 'X' ? 'E' : 'F') }}</td>
        </tr>
        <tr>
            <td><strong>NIS / NISN</strong></td>
            <td>:</td>
            <td>{{ $student->nis }} / {{ $student->nisn }}</td>
            <td><strong>Semester / TA</strong></td>
            <td>:</td>
            <td>{{ isset($semesterNumber) ? 'Semester '.$semesterNumber.' - ' : '' }}{{ $academicYear->year ?? ($academicYear->name ?? '-') }}</td>
        </tr>
    </table>

    <h3 style="font-size: 14px; margin-bottom: 5px;">A. Nilai Akademik</h3>
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="28%">Mata Pelajaran</th>
                <th width="10%">KKM</th>
                <th width="12%">Nilai Akhir</th>
                <th width="15%">Predikat</th>
                <th width="30%">Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades ?? [] as $index => $grade)
                @php
                    $tpText = $tpDrafts[$grade->subject_id] ?? $grade->description ?? null;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $grade?->subject?->name }}</td>
                    <td class="text-center">{{ $grade->subject->kkm ?? 75 }}</td>
                    <td class="text-center"><strong>{{ number_format((float) $grade->score, 0) }}</strong></td>
                    <td class="text-center">{{ $grade->predikat }}</td>
                    <td style="font-size: 10px;">{{ $tpText ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada data nilai akademik.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(($reportType ?? 'pas') !== 'pts')
        @if($reportCard)
        <table class="table-data" style="width: 50%; float: right;">
            <tr>
                <td width="60%"><strong>Total Nilai</strong></td>
                <td width="40%" class="text-center">{{ number_format($reportCard->total_score, 0) }}</td>
            </tr>
            <tr>
                <td><strong>Rata-Rata</strong></td>
                <td class="text-center">{{ number_format($reportCard->average_score, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Peringkat di Kelas</strong></td>
                <td class="text-center">{{ $reportCard->rank_in_class }}</td>
            </tr>
        </table>
        <div style="clear: both;"></div>
        @endif

        @if(isset($extracurriculars) && $extracurriculars->isNotEmpty())
        <h3 style="font-size: 14px; margin-bottom: 5px; margin-top: 10px;">B. Ekstrakurikuler</h3>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="35%">Kegiatan Ekstrakurikuler</th>
                    <th width="15%">Predikat</th>
                    <th width="45%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($extracurriculars as $idx => $ekskul)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $ekskul->member?->extracurricular?->name ?? '-' }}</td>
                        <td class="text-center">{{ $ekskul->grade ?? '-' }}</td>
                        <td>{{ $ekskul->description ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <h3 style="font-size: 14px; margin-bottom: 5px; margin-top: 10px;">{{ isset($extracurriculars) && $extracurriculars->isNotEmpty() ? 'C' : 'B' }}. Ketidakhadiran</h3>
        <table class="table-data" style="width: 50%;">
            <tr>
                <td width="60%">Sakit</td>
                <td width="40%" class="text-center">{{ $attendance->sakit ?? 0 }} hari</td>
            </tr>
            <tr>
                <td>Izin</td>
                <td class="text-center">{{ $attendance->izin ?? 0 }} hari</td>
            </tr>
            <tr>
                <td>Tanpa Keterangan (Alpa)</td>
                <td class="text-center">{{ $attendance->alpa ?? 0 }} hari</td>
            </tr>
        </table>

        <h3 style="font-size: 14px; margin-bottom: 5px;">{{ isset($extracurriculars) && $extracurriculars->isNotEmpty() ? 'D' : 'C' }}. Catatan Wali Kelas</h3>
        <div class="notes-box">
            @if($reportCard && $reportCard->homeroom_notes)
                {{ $reportCard->homeroom_notes }}
            @else
                <i style="color: #666;">- Tidak ada catatan -</i>
            @endif
        </div>
    @endif

    <!-- Tanda Tangan -->
    <div class="footer-section">
        <div class="signature-box" style="float: left;">
            <p>Mengetahui,<br>Orang Tua/Wali</p>
            <div class="signature-space"></div>
            <p>(............................................)</p>
        </div>
        
        <div class="signature-box" style="float: right;">
            <p>Semarang, {{ ($template?->issued_date ?? now())->translatedFormat('d F Y') }}<br>Wali Kelas</p>
            <div class="signature-space"></div>
            <p><strong>{{ $waliKelas?->name ?? '............................................' }}</strong></p>
        </div>
        
        <div style="clear: both;"></div>
        
        <div class="signature-box" style="margin: 0 auto; display: block; margin-top: -20px;">
            <p>Kepala Sekolah</p>
            <div class="signature-space"></div>
            <p><strong>{{ $kepalaSekolah?->name ?? '............................................' }}</strong></p>
            @if(!empty($principalNip))
                <p style="font-size: 10px; margin-top: 2px;">NIP. {{ $principalNip }}</p>
            @endif
        </div>
    </div>

</body>
</html>
