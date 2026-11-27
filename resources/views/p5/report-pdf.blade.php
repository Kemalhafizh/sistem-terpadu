<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor Projek P5 - {{ $student->user?->name ?? $student->nis }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #0f172a; line-height: 1.45; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { font-size: 14px; margin: 0; text-transform: uppercase; }
        .header h2 { font-size: 12px; margin: 4px 0 0; font-weight: normal; }
        .meta { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .meta td { padding: 3px 6px; vertical-align: top; }
        .project-box { margin-bottom: 18px; page-break-inside: avoid; }
        .project-title { font-size: 12px; font-weight: bold; margin: 0 0 4px; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.grid th, table.grid td { border: 1px solid #475569; padding: 6px 8px; vertical-align: top; text-align: left; }
        table.grid th { background-color: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        .note-box { border: 1px solid #475569; padding: 8px; margin-top: 8px; background: #f8fafc; }
        .signatures { width: 100%; margin-top: 28px; border-collapse: collapse; }
        .signatures td { width: 33%; text-align: center; vertical-align: top; padding-top: 8px; }
        .sig-space { height: 56px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $report['school_name'] }}</h1>
        <h2>RAPOR PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)</h2>
    </div>

    <table class="meta">
        <tr>
            <td width="20%"><strong>Nama Peserta Didik</strong></td>
            <td width="35%">: {{ $student->user?->name ?? '-' }}</td>
            <td width="18%"><strong>Kelas / Fase</strong></td>
            <td width="27%">: {{ $report['classroom']?->name ?? '-' }} / Fase {{ $report['phase'] ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>NIS / NISN</strong></td>
            <td>: {{ $student->nis ?? '-' }} / {{ $student->nisn ?? '-' }}</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: {{ $report['school_year']?->name ?? '-' }}</td>
        </tr>
    </table>

    <p style="font-size: 10px; margin-bottom: 12px;">
        <strong>Keterangan Predikat:</strong>
        MB = Mulai Berkembang · SB = Sedang Berkembang · BSH = Berkembang Sesuai Harapan · SAB = Sangat Berkembang
    </p>

    @forelse ($report['projects'] as $index => $proj)
        <div class="project-box">
            <div class="project-title">Projek {{ $index + 1 }}: {{ $proj['title'] }} ({{ $proj['theme_label'] }})</div>
            @if ($proj['description'])
                <div style="margin-bottom: 6px;">{{ $proj['description'] }}</div>
            @endif

            <table class="grid">
                <thead>
                    <tr>
                        <th width="28%">Dimensi & Elemen</th>
                        <th width="50%">Capaian Akhir Fase</th>
                        <th width="22%">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proj['dimensions'] as $dim)
                        <tr>
                            <td>
                                <strong>{{ $dim['dimension_name'] }}</strong><br>
                                <span>{{ $dim['element'] }}{{ $dim['sub_element'] ? ' - '.$dim['sub_element'] : '' }}</span>
                            </td>
                            <td>{{ $dim['target_achievement'] }}</td>
                            <td>
                                @if ($dim['predicate'])
                                    <strong>{{ $dim['predicate'] }}</strong> ({{ $dim['predicate_label'] }})
                                @else
                                    Belum dinilai
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="note-box">
                <strong>Catatan Proses:</strong><br>
                {{ $proj['process_notes'] ?? 'Belum ada catatan proses projek.' }}
            </div>
        </div>
    @empty
        <p>Belum ada data Projek P5 pada tahun ajaran ini.</p>
    @endforelse

    <table class="signatures">
        <tr>
            <td>
                Orang Tua / Wali
                <div class="sig-space"></div>
                ( ........................................ )
            </td>
            <td>
                Wali Kelas
                <div class="sig-space"></div>
                <strong>{{ $report['homeroom_teacher_name'] ?? '........................................' }}</strong>
            </td>
            <td>
                Kepala Sekolah
                <div class="sig-space"></div>
                <strong>{{ $report['principal_name'] ?: '........................................' }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
