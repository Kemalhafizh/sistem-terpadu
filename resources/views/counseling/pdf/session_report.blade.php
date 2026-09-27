<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Konseling - {{ $session->student->user->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 0px 20px;
        }
        .header {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 11pt;
            font-weight: normal;
            margin: 5px 0 0 0;
        }
        .header .address {
            font-size: 8pt;
            font-style: italic;
            margin-top: 5px;
        }
        .title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 25px;
        }
        .title-sub {
            text-align: center;
            font-size: 9pt;
            margin-top: -20px;
            margin-bottom: 25px;
            font-weight: bold;
            color: #555;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .meta-table td.label {
            width: 25%;
            font-weight: bold;
        }
        .meta-table td.colon {
            width: 3%;
        }
        .meta-table td.value {
            width: 72%;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 5px 10px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-left: 4px solid #4f46e5;
            text-transform: uppercase;
        }
        .content-box {
            padding: 5px 10px;
            font-size: 10.5pt;
            text-align: justify;
            white-space: pre-wrap;
            border: 1px solid #e5e7eb;
            background-color: #fafafa;
            min-height: 80px;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding-bottom: 10px;
        }
        .signature-space {
            height: 70px;
        }
        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signer-title {
            font-size: 9.5pt;
            color: #555;
            margin-top: 2px;
        }
        .footer-note {
            margin-top: 40px;
            text-align: center;
            font-size: 8pt;
            color: #888;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Kop Surat Resmi -->
    <div class="header">
        <h1>{{ mb_strtoupper(school_foundation_name(false)) }}</h1>
        <h1>{{ mb_strtoupper(school_name()) }}</h1>
        @if(school_accreditation())<h2>TERAKREDITASI {{ mb_strtoupper(school_accreditation()) }}</h2>@endif
        <div class="address">
            {{ school_contact_line(['address', 'phone', 'email'], ' • ') }}
        </div>
    </div>

    <!-- Judul Berita Acara -->
    <div class="title">BERITA ACARA BIMBINGAN & KONSELING</div>
    <div class="title-sub">Nomor: BK/{{ $session->scheduled_at->format('Y/m') }}/{{ str_pad($session->id, 4, '0', STR_PAD_LEFT) }}</div>

    <p style="font-size: 10.5pt; text-align: justify; margin-bottom: 20px;">
        Pada hari ini, <strong>{{ $session->scheduled_at->translatedFormat('l') }}</strong> tanggal <strong>{{ $session->scheduled_at->translatedFormat('d F Y') }}</strong>, telah diselenggarakan sesi layanan Bimbingan dan Konseling (BK) di Unit Bimbingan Konseling {{ school_name() }}, dengan detail pelaksanaan sebagai berikut:
    </p>

    <!-- Meta Identitas -->
    <table class="meta-table">
        <tr>
            <td class="label">Nama Lengkap Murid</td>
            <td class="colon">:</td>
            <td class="value"><strong>{{ $session->student->user->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Siswa (NIS)</td>
            <td class="colon">:</td>
            <td class="value">{{ $session->student->nis }}</td>
        </tr>
        <tr>
            <td class="label">Kelas / Rombel</td>
            <td class="colon">:</td>
            <td class="value">{{ $session->student->classroom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Sesi Layanan</td>
            <td class="colon">:</td>
            <td class="value">{{ strtoupper($session->type) }}</td>
        </tr>
        <tr>
            <td class="label">Kategori Permasalahan</td>
            <td class="colon">:</td>
            <td class="value">{{ strtoupper($session->category) }}</td>
        </tr>
        <tr>
            <td class="label">Konselor / Guru BK</td>
            <td class="colon">:</td>
            <td class="value">{{ $session->counselor->name ?? '-' }}</td>
        </tr>
    </table>

    <!-- Bagian 1: Uraian Masalah -->
    <div class="section-title">1. Uraian Latar Belakang / Keluhan Pengajuan</div>
    <div class="content-box">{{ $session->reason }}</div>

    <!-- Bagian 2: Catatan Bimbingan -->
    <div class="section-title">2. Hasil Konseling / Uraian Masalah Psikologis</div>
    <div class="content-box">{{ $session->session_notes }}</div>

    <!-- Bagian 3: Rencana Tindakan -->
    <div class="section-title">3. Rencana Tindakan & Komitmen Siswa (Action Plan)</div>
    <div class="content-box">{{ $session->action_plan }}</div>

    <!-- Bagian Tanda Tangan -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div>Semarang, {{ now()->translatedFormat('d F Y') }}</div>
                    <div style="margin-top: 5px;">Siswa Bersangkutan,</div>
                    <div class="signature-space"></div>
                    <div class="signer-name">{{ $session->student->user->name }}</div>
                    <div class="signer-title">NIS: {{ $session->student->nis }}</div>
                </td>
                <td>
                    <div style="visibility: hidden;">Tanggal</div>
                    <div style="margin-top: 5px;">Guru Bimbingan Konseling (BK),</div>
                    <div class="signature-space"></div>
                    <div class="signer-name">{{ $session->counselor->name ?? 'Petugas BK' }}</div>
                    <div class="signer-title">Konselor SMA ISA 1</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 30px;">
                    <div>Mengetahui,</div>
                    <div>Kepala Sekolah {{ school_short_name() }},</div>
                    <div class="signature-space"></div>
                    <div class="signer-name">{{ signature_name(school_principal_name()) }}</div>
                    <div class="signer-title">{{ signature_nip(school_principal_nip()) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Note Rahasia -->
    <div class="footer-note">
        Dokumen ini bersifat RAHASIA dan dilindungi oleh kode etik Bimbingan & Konseling Sekolah.<br>
        Dicetak secara otomatis melalui Sistem Terpadu {{ school_short_name() }}.
    </div>

</div>

</body>
</html>
