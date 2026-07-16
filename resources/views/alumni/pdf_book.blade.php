<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Alumni Angkatan {{ $year }}</title>
    <style>
        body {
            font-family: Georgia, 'Times New Roman', serif;
            color: #000;
            line-height: 1.5;
            padding: 10px;
        }
        .header {
            text-align: center;
            border-bottom: 4px double #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .header h1 {
            font-size: 20pt;
            text-transform: uppercase;
            margin: 0;
            font-weight: bold;
        }
        .header h2 {
            font-size: 14pt;
            text-transform: uppercase;
            margin: 5px 0 0 0;
            font-weight: normal;
        }
        .header p {
            font-size: 9pt;
            font-style: italic;
            margin: 5px 0 0 0;
        }
        .title {
            text-align: center;
            margin-bottom: 30px;
        }
        .title h3 {
            font-size: 13pt;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .alumni-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 15px;
        }
        .alumni-table th, .alumni-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        .alumni-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 11pt;
        }
        .footer p {
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Sekolah -->
    <div class="header">
        <h1>SMA Islam Sultan Agung 1</h1>
        <h2>Yayasan Badan Wakaf Sultan Agung</h2>
        <p>Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="title">
        <h3>Buku Profil Alumni Angkatan Tahun {{ $year }}</h3>
    </div>

    <!-- Tabel Data Alumni -->
    <table class="alumni-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 25%;">Nama Alumni</th>
                <th style="width: 15%;">NISN</th>
                <th style="width: 15%;">Aktivitas</th>
                <th style="width: 25%;">Instansi / Program Studi</th>
                <th style="width: 15%;">Kontak</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($alumni as $al)
                <tr>
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td style="font-weight: bold;">{{ $al->student->name }}</td>
                    <td>{{ $al->student->nisn ?? '-' }}</td>
                    <td>
                        @if($al->current_activity === 'study')
                            Kuliah
                        @elseif($al->current_activity === 'work')
                            Bekerja
                        @elseif($al->current_activity === 'entrepreneur')
                            Wirausaha
                        @elseif($al->current_activity === 'unemployed')
                            Mencari Kerja
                        @else
                            Lainnya
                        @endif
                    </td>
                    <td>
                        {{ $al->institution_name ?? '-' }}
                        @if($al->current_position)
                            <br><span style="font-size: 8.5pt; color: #555;">({{ $al->current_position }})</span>
                        @endif
                    </td>
                    <td style="font-size: 8.5pt;">
                        {{ $al->email ?? '-' }}<br>
                        {{ $al->phone_number ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Semarang, {{ now()->translatedFormat('d F Y') }}</p>
        <p style="margin-top: 5px; margin-bottom: 60px;">Kepala Sekolah,</p>
        <p style="font-weight: bold; text-decoration: underline;">Drs. H. Soetarto, M.Pd.</p>
    </div>

</body>
</html>
