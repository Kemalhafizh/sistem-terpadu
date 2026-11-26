<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Peringatan {{ $letter->level }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.6; padding: 24px; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 24px; }
        .header h1 { font-size: 16px; margin: 0 0 4px 0; text-transform: uppercase; }
        .header p { font-size: 11px; margin: 0; color: #4b5563; }
        .title { text-align: center; margin-bottom: 20px; }
        .title h2 { font-size: 15px; margin: 0; text-decoration: underline; text-transform: uppercase; }
        .meta { width: 100%; border-collapse: collapse; margin: 16px 0; }
        .meta td { padding: 4px 8px; vertical-align: top; }
        .meta td.label { width: 170px; font-weight: bold; }
        .box { border: 1px solid #d1d5db; padding: 12px; background: #f9fafb; margin: 16px 0; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { width: 50%; text-align: center; vertical-align: top; }
        .sig-space { height: 64px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $schoolName }}</h1>
        <p>Bidang Kesiswaan dan Bimbingan Konseling</p>
    </div>

    <div class="title">
        <h2>SURAT PERINGATAN ({{ $letter->level }})</h2>
        <p>Tanggal Terbit: {{ $letter->issued_at?->translatedFormat('d F Y') ?? '-' }}</p>
    </div>

    <p>Surat Peringatan ini diterbitkan kepada peserta didik berikut:</p>

    <table class="meta">
        <tr>
            <td class="label">Nama Peserta Didik</td>
            <td>: {{ $student?->user?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIS / NISN</td>
            <td>: {{ $student?->nis ?? '-' }} / {{ $student?->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kelas / Rombel</td>
            <td>: {{ $student?->classroom?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ambang Poin {{ $letter->level }}</td>
            <td>: {{ $letter->point_threshold }} Poin (Total Saat Ini: {{ $student?->total_points ?? 0 }} Poin)</td>
        </tr>
    </table>

    <div class="box">
        <strong>Catatan / Keterangan Pembinaan:</strong><br>
        {{ $letter->notes ?: 'Peserta didik diwajibkan mengikuti sesi pembinaan bersama Guru BK dan mematuhi tata tertib sekolah.' }}
    </div>

    <p>
        Orang tua/wali murid dimohon mendampingi pembinaan peserta didik bersama Wali Kelas dan Guru Bimbingan Konseling agar pelanggaran tidak terulang kembali.
    </p>

    <table class="signatures">
        <tr>
            <td>
                Orang Tua / Wali Murid,
                <div class="sig-space">
                    @if($letter->signed_at)
                        <br><small>Ditandatangani digital: {{ $letter->signed_at->format('d/m/Y H:i') }}</small>
                    @endif
                </div>
                <strong>{{ $letter->parent?->name ?? ($student?->parent?->name ?? '........................') }}</strong>
            </td>
            <td>
                Mengetahui,<br>Kepala Sekolah
                <div class="sig-space"></div>
                <strong>{{ $principalName }}</strong>
            </td>
        </tr>
    </table>
</body>
</html>
