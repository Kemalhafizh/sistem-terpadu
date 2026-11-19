<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Induk Siswa - {{ $student->user?->name ?? '-' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a202c; line-height: 1.45; margin: 20px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px; }
        .kop h2 { margin: 0; font-size: 12px; text-transform: uppercase; }
        .kop h1 { margin: 2px 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop p { margin: 0; font-size: 9px; color: #333; }
        .title-block { text-align: center; margin-bottom: 14px; }
        .title-block h3 { margin: 0; font-size: 13px; text-transform: uppercase; text-decoration: underline; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; background: #edf2f7; padding: 4px 8px; margin: 12px 0 6px; border-left: 3px solid #2b6cb0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.meta td { padding: 2px 4px; vertical-align: top; }
        table.meta td.label { width: 28%; color: #4a5568; }
        table.meta td.sep { width: 2%; }
        table.grid th, table.grid td { border: 1px solid #cbd5e0; padding: 4px 6px; }
        table.grid th { background: #f7fafc; text-align: left; font-size: 9px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .sign-wrapper { width: 100%; margin-top: 22px; }
        .sign-box { width: 42%; float: right; text-align: center; }
        .sign-space { height: 55px; }
    </style>
</head>
<body>
    <div class="kop">
        <h2>{{ school_foundation_name(false) }}</h2>
        <h1>{{ school_name() }}</h1>
        <p>NPSN: {{ school_npsn() ?: '-' }} @if(school_accreditation()) | Terakreditasi {{ school_accreditation() }} @endif</p>
        <p>{{ school_contact_line() }}</p>
    </div>

    <div class="title-block">
        <h3>BUKU INDUK PESERTA DIDIK</h3>
        <div>Nomor Induk: {{ $student->nis ?: '-' }} / NISN: {{ $student->nisn ?: '-' }}</div>
    </div>

    <div class="section-title">A. Identitas Peserta Didik</div>
    <table class="meta">
        <tr><td class="label">Nama Lengkap</td><td class="sep">:</td><td><strong>{{ $student->user?->name ?? '-' }}</strong></td></tr>
        <tr><td class="label">NIS / NISN / NIK</td><td class="sep">:</td><td>{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }} / {{ $student->nik ?: '-' }}</td></tr>
        <tr><td class="label">Jenis Kelamin</td><td class="sep">:</td><td>{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
        <tr><td class="label">Tempat, Tanggal Lahir</td><td class="sep">:</td><td>{{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->translatedFormat('d F Y') : '-' }}</td></tr>
        <tr><td class="label">Nama Ibu Kandung</td><td class="sep">:</td><td>{{ $student->mother_name ?: '-' }}</td></tr>
        <tr><td class="label">Orang Tua / Wali Terdaftar</td><td class="sep">:</td><td>{{ $student->parent?->name ?? '-' }} @if($student->parent?->phone_number) ({{ $student->parent->phone_number }}) @endif</td></tr>
        <tr><td class="label">Alamat Tempat Tinggal</td><td class="sep">:</td><td>{{ $student->address ?: '-' }}</td></tr>
        <tr><td class="label">Rombel Saat Ini / Status</td><td class="sep">:</td><td>{{ $student->classroom?->name ?? '-' }} ({{ strtoupper((string) ($student->status ?? 'active')) }})</td></tr>
    </table>

    <div class="section-title">B. Riwayat Penempatan Rombongan Belajar</div>
    <table class="grid">
        <thead>
            <tr>
                <th>Tahun Ajaran</th>
                <th>Rombongan Belajar</th>
                <th>Wali Kelas</th>
                <th>Periode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($enrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->schoolYear?->name ?? '-' }}</td>
                    <td>{{ $enrollment->classroom?->name ?? '-' }}</td>
                    <td>{{ $enrollment->classroom?->teacher?->name ?? '-' }}</td>
                    <td>{{ $enrollment->started_on?->format('d/m/Y') ?? '-' }} s/d {{ $enrollment->ended_on?->format('d/m/Y') ?? 'Aktif' }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', (string) $enrollment->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Belum ada riwayat penempatan rombel.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">C. Riwayat Mutasi &amp; Kehadiran</div>
    <p>Rekap Kehadiran Kumulatif: Hadir {{ $attendanceSummary['H'] }} | Sakit {{ $attendanceSummary['S'] }} | Izin {{ $attendanceSummary['I'] }} | Alpa {{ $attendanceSummary['A'] }}</p>
    @if($mutations->isNotEmpty())
        <table class="grid">
            <thead>
                <tr><th>Tanggal</th><th>Jenis</th><th>Sekolah Tujuan/Asal</th><th>Alasan</th></tr>
            </thead>
            <tbody>
                @foreach($mutations as $mutation)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mutation->mutation_date)->format('d/m/Y') }}</td>
                        <td>{{ $mutation->type === 'in' ? 'Mutasi Masuk' : 'Mutasi Keluar' }}</td>
                        <td>{{ $mutation->target_school ?: '-' }}</td>
                        <td>{{ $mutation->reason ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">D. Rekap Nilai Akademik per Semester</div>
    @forelse($semesterSummaries as $summary)
        <p style="margin: 6px 0 3px;">
            <strong>TA {{ $summary['academic_year']?->year ?? '-' }} &ndash; Semester {{ ucfirst((string) ($summary['academic_year']?->semester ?? '-')) }}</strong>
            @if($summary['report_card'])
                (Rata-rata Rapor: {{ number_format((float) $summary['report_card']->average_score, 2) }}@if($summary['report_card']->rank_in_class), Peringkat #{{ $summary['report_card']->rank_in_class }}@endif)
            @endif
        </p>
        @if($summary['subjects']->isNotEmpty())
            <table class="grid">
                <thead><tr><th>Mata Pelajaran</th><th>Kelompok</th><th class="text-right">KKM</th><th class="text-right">Rata-rata Nilai</th></tr></thead>
                <tbody>
                    @foreach($summary['subjects'] as $subjectRow)
                        <tr>
                            <td>{{ $subjectRow['subject_name'] }}</td>
                            <td>{{ $subjectRow['group'] }}</td>
                            <td class="text-right">{{ $subjectRow['kkm'] }}</td>
                            <td class="text-right">{{ $subjectRow['average_score'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @empty
        <p>Belum ada catatan nilai akademik.</p>
    @endforelse

    <div class="section-title">E. Prestasi, Ekstrakurikuler &amp; Kelulusan</div>
    @if($achievements->isNotEmpty())
        <table class="grid">
            <thead><tr><th>Judul Prestasi</th><th>Tingkat</th><th>Penyelenggara</th><th>Tanggal</th></tr></thead>
            <tbody>
                @foreach($achievements as $ach)
                    <tr>
                        <td>{{ $ach->title }}</td>
                        <td>{{ strtoupper((string) $ach->level) }}</td>
                        <td>{{ $ach->organizer }}</td>
                        <td>{{ $ach->event_date?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Belum ada catatan prestasi tervalidasi.</p>
    @endif

    @if($extracurriculars->isNotEmpty())
        <table class="grid">
            <thead><tr><th>Ekstrakurikuler</th><th>Tahun Ajaran</th><th>Predikat / Nilai</th></tr></thead>
            <tbody>
                @foreach($extracurriculars as $extra)
                    <tr>
                        <td>{{ $extra->member?->extracurricular?->name ?? '-' }}</td>
                        <td>{{ $extra->academicYear?->year ?? '-' }} ({{ $extra->academicYear?->semester ?? '-' }})</td>
                        <td>{{ $extra->grade ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($alumni)
        <p><strong>Status Kelulusan / Alumni:</strong> Lulus Tahun {{ $alumni->graduation_year }} &mdash; {{ $alumni->current_activity ?: '-' }} @if($alumni->institution_name) ({{ $alumni->institution_name }}) @endif</p>
    @endif

    <div class="sign-wrapper">
        <div class="sign-box">
            Semarang, {{ $printedAt->translatedFormat('d F Y') }}<br>
            Kepala Sekolah,
            <div class="sign-space"></div>
            <strong>{{ $principal['name'] }}</strong><br>
            @if(!empty($principal['nip']))
                NIP. {{ $principal['nip'] }}
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
