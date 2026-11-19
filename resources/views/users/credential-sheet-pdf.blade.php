<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lembar Akun Rombel {{ $classroom->name }} - {{ $school_name }}</title>
    <style>
        @page { margin: 18px 22px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1f2937; line-height: 1.35; }
        .header { border-bottom: 2px solid #065f46; padding-bottom: 8px; margin-bottom: 12px; }
        .title { font-size: 15px; font-weight: bold; color: #064e3b; margin: 0 0 2px 0; }
        .subtitle { font-size: 10px; color: #4b5563; margin: 0; }
        .notice { background: #fef3c7; border: 1px solid #f59e0b; padding: 7px 10px; margin-bottom: 10px; font-size: 9.5px; color: #78350f; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background-color: #f3f4f6; font-weight: bold; font-size: 9px; text-transform: uppercase; color: #374151; }
        .mono { font-family: monospace; font-size: 10px; font-weight: bold; }
        .muted { color: #6b7280; font-style: italic; }
        .footer { margin-top: 14px; font-size: 9px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $school_name }} &mdash; Lembar Kredensial Akun Rombel {{ $classroom->name }}</p>
        <p class="subtitle">
            Wali Kelas: {{ $classroom->teacher?->name ?? '-' }} &bull;
            Tahun Ajaran: {{ $classroom->academic_year ?? '-' }} &bull;
            Dicetak: {{ $generated_at }}
        </p>
    </div>

    <div class="notice">
        <strong>Penting (RAHASIA):</strong> Bagikan informasi ini langsung kepada siswa/orang tua terkait.
        Pengguna baru dengan <em>password sementara</em> wajib langsung mengganti kata sandi saat login pertama kali ke web Sulaone atau aplikasi Android Hanyakra.
        Akun yang sudah diaktifkan tetap menggunakan kata sandi pribadi masing-masing.
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Siswa</th>
                <th>NIS / NISN</th>
                <th>ID Login Siswa</th>
                <th>Password Sementara Siswa</th>
                <th>Nama Orang Tua / Wali</th>
                <th>ID Login Ortu (No. HP)</th>
                <th>Password Sementara Ortu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $row['student_name'] }}</strong></td>
                    <td class="mono">{{ $row['nis'] ?: '-' }} / {{ $row['nisn'] ?: '-' }}</td>
                    <td class="mono">{{ $row['student_login'] }}</td>
                    <td>
                        @if ($row['student_temp_password'])
                            <span class="mono">{{ $row['student_temp_password'] }}</span>
                        @elseif ($row['student_activated'])
                            <span class="muted">Sudah aktif (sandi pribadi)</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                    <td>{{ $row['parent_name'] ?: '-' }}</td>
                    <td class="mono">{{ $row['parent_login'] ?: '-' }}</td>
                    <td>
                        @if ($row['parent_temp_password'])
                            <span class="mono">{{ $row['parent_temp_password'] }}</span>
                        @elseif ($row['parent_activated'])
                            <span class="muted">Sudah aktif (sandi pribadi)</span>
                        @else
                            <span class="muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Belum ada siswa aktif pada rombel ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Total Siswa: {{ count($rows) }} &bull; Password sementara diterbitkan ulang: {{ $reset_count }} akun
    </div>
</body>
</html>
