<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Peserta SPMB - {{ $registration->registration_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            background-color: #fff;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .card {
            border: 2px solid #047857;
            border-radius: 12px;
            padding: 20px;
            max-width: 550px;
            margin: 0 auto;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #047857;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
            color: #047857;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
        }
        .content {
            display: flex;
            margin-bottom: 15px;
        }
        .photo-container {
            width: 100px;
            text-align: center;
            margin-right: 20px;
        }
        .photo {
            width: 90px;
            height: 120px;
            border: 1px solid #ccc;
            object-fit: cover;
            border-radius: 4px;
            background-color: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .details {
            flex-grow: 1;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 4px 0;
            vertical-align: top;
        }
        .details td.label {
            font-weight: bold;
            color: #666;
            width: 130px;
        }
        .footer {
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            margin-top: 15px;
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }
        .print-btn {
            display: block;
            width: 120px;
            margin: 20px auto 0 auto;
            padding: 8px 15px;
            background-color: #047857;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
        }
        @media print {
            .print-btn {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="card">
        <div class="header">
            <h2>KARTU PESERTA SELEKSI MASUK (SPMB)</h2>
            <p>SMA Islam Sultan Agung 1 Semarang</p>
        </div>

        <div class="content">
            <div class="photo-container">
                @if($registration->photo_path)
                    <img src="{{ asset('storage/' . $registration->photo_path) }}" alt="Foto" class="photo">
                @else
                    <div class="photo">Pas Foto<br>3x4</div>
                @endif
            </div>
            
            <div class="details">
                <table>
                    <tr>
                        <td class="label">No. Pendaftaran</td>
                        <td>: <strong>{{ $registration->registration_number }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Nama Lengkap</td>
                        <td>: {{ $registration->full_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">NISN</td>
                        <td>: {{ $registration->nisn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Jalur Masuk</td>
                        <td>: {{ $registration->spmbTrack->name ?? 'Reguler' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Gelombang</td>
                        <td>: {{ $registration->spmbWave->name ?? 'Gelombang 1' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Asal Sekolah</td>
                        <td>: {{ $registration->previous_school }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Daftar</td>
                        <td>: {{ $registration->created_at->format('d-m-Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            <strong>Petunjuk Penting:</strong>
            <ol style="margin: 5px 0 0 15px; padding: 0;">
                <li>Simpan kartu peserta ini dengan baik sebagai bukti pendaftaran resmi.</li>
                <li>Gunakan Nomor Pendaftaran di atas untuk memeriksa pengumuman seleksi di website SPMB.</li>
                <li>Jika memilih Jalur Prestasi/Tahfidz, harap membawa berkas asli sertifikat/piagam saat tes wawancara.</li>
                <li>Informasi lebih lanjut hubungi Panitia SPMB SMA Islam Sultan Agung 1 Semarang.</li>
            </ol>
        </div>
    </div>

    <button onclick="window.print()" class="print-btn">Cetak Kartu</button>

</body>
</html>
