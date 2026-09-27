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
    </style>
</head>
<body>

    <div class="header">
        <h1>LAPORAN HASIL BELAJAR SISWA</h1>
        <h1>{{ mb_strtoupper(school_name()) }}</h1>
        <p>{{ school_address() }}</p>
    </div>

    <table class="student-info">
        <tr>
            <td width="15%"><strong>Nama Siswa</strong></td>
            <td width="2%">:</td>
            <td width="43%">{{ $student->name }}</td>
            <td width="15%"><strong>Kelas</strong></td>
            <td width="2%">:</td>
            <td width="23%">{{ $student->classroom ? $student?->classroom?->name : '-' }}</td>
        </tr>
        <tr>
            <td><strong>NIS / NISN</strong></td>
            <td>:</td>
            <td>{{ $student->nis }} / {{ $student->nisn }}</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>:</td>
            <td>{{ $academicYear->name }}</td>
        </tr>
    </table>

    <h3 style="font-size: 14px; margin-bottom: 5px;">A. Nilai Akademik</h3>
    <table class="table-data">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Mata Pelajaran</th>
                <th width="15%">KKM</th>
                <th width="15%">Nilai Akhir</th>
                <th width="30%">Predikat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($grades ?? [] as $index => $grade)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $grade?->subject?->name }}</td>
                    <td class="text-center">{{ $grade->subject->kkm ?? 75 }}</td>
                    <td class="text-center"><strong>{{ number_format($grade->score, 0) }}</strong></td>
                    <td class="text-center">{{ $grade->predikat }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data nilai akademik.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

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

    <h3 style="font-size: 14px; margin-bottom: 5px; margin-top: 10px;">B. Ketidakhadiran</h3>
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

    <h3 style="font-size: 14px; margin-bottom: 5px;">C. Catatan Wali Kelas</h3>
    <div class="notes-box">
        @if($reportCard && $reportCard->homeroom_notes)
            {{ $reportCard->homeroom_notes }}
        @else
            <i style="color: #666;">- Tidak ada catatan -</i>
        @endif
    </div>

    <!-- Tanda Tangan -->
    <div class="footer-section">
        <div class="signature-box" style="float: left;">
            <p>Mengetahui,<br>Orang Tua/Wali</p>
            <div class="signature-space"></div>
            <p>(............................................)</p>
        </div>
        
        <div class="signature-box" style="float: right;">
            <p>Semarang, {{ date('d F Y') }}<br>Wali Kelas</p>
            <div class="signature-space"></div>
            <p><strong>{{ $waliKelas?->name ?? '............................................' }}</strong></p>
        </div>
        
        <div style="clear: both;"></div>
        
        <div class="signature-box" style="margin: 0 auto; display: block; margin-top: -20px;">
            <p>Kepala Sekolah</p>
            <div class="signature-space"></div>
            <p><strong>{{ $kepalaSekolah?->name ?? '............................................' }}</strong></p>
        </div>
    </div>

</body>
</html>
