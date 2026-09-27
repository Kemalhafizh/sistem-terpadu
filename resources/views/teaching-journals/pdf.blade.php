<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Jurnal Mengajar Guru</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 10px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; text-transform: uppercase; }
        .header p { margin: 3px 0 0 0; font-size: 10px; color: #666; }
        .meta-table { width: 100%; margin-bottom: 15px; border: 0; }
        .meta-table td { padding: 4px; border: 0; }
        .main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .main-table th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; font-weight: bold; text-align: left; }
        .main-table td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
        .signature-section { width: 100%; margin-top: 50px; text-align: right; }
        .signature-box { display: inline-block; width: 200px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ mb_strtoupper(school_name()) }}</h2>
        <p>{{ school_contact_line() }}</p>
        <h3 style="margin: 15px 0 0 0; text-decoration: underline;">REKAPITULASI JURNAL HARIAN MENGAJAR GURU</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Bulan / Tahun:</strong></td>
            <td width="35%">{{ date('F', mktime(0,0,0, $request->month, 1)) }} / {{ $request->year }}</td>
            <td width="15%"><strong>Tanggal Cetak:</strong></td>
            <td width="35%">{{ date('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Hari / Tanggal</th>
                <th width="15%">Nama Guru</th>
                <th width="15%">Kelas & Mapel</th>
                <th width="8%">Jam Ke</th>
                <th width="25%">Materi Pokok / Topik</th>
                <th width="10%">Metode</th>
                <th width="15%">Hambatan / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($journals as $j)
            <tr>
                <td>{{ $j->teaching_date->format('l, d M Y') }}</td>
                <td><strong>{{ $j->teacher->name }}</strong></td>
                <td>{{ $j->classroom->name }}<br><span style="color:#666; font-size:9px;">{{ $j->subject->name }}</span></td>
                <td style="text-align: center;">{{ $j->jam_ke ?: '-' }}</td>
                <td>
                    <strong>{{ $j->topic }}</strong>
                    @if($j->learning_activity)
                        <div style="font-size:9px; color:#555; margin-top:3px;">Act: {{ $j->learning_activity }}</div>
                    @endif
                </td>
                <td>{{ $j->learning_method ?: '-' }}</td>
                <td>{{ $j->obstacles ?: ($j->notes ?: '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <p>Semarang, {{ date('d F Y') }}</p>
            <p>Waka Bidang Kurikulum</p>
            <br><br><br>
            <p><strong>_______________________</strong></p>
            <p>NIP. -</p>
        </div>
    </div>
</body>
</html>
