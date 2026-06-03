<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Biodata Siswa - {{ $student->user->name }}</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #1a202c; 
            line-height: 1.5; 
            margin: 0; 
            padding: 0;
            background-color: #fff;
        }
        .container { padding: 40px; }
        
        /* Premium Header */
        .header { 
            position: relative;
            padding-bottom: 20px;
            margin-bottom: 30px;
            border-bottom: 2px solid #2d3748;
        }
        .school-logo {
            position: absolute;
            top: 0;
            left: 0;
            width: 70px;
            height: 70px;
        }
        .school-info {
            margin-left: 90px;
        }
        .school-name {
            font-size: 20px;
            font-weight: 900;
            color: #2c5282;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .school-details {
            font-size: 9px;
            color: #718096;
            margin-top: 4px;
        }

        /* Title */
        .doc-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .doc-title h2 {
            display: inline-block;
            font-size: 16px;
            font-weight: 800;
            color: #1a202c;
            padding: 5px 20px;
            border-bottom: 2px solid #edf2f7;
            text-transform: uppercase;
        }

        /* Layout Grid */
        .flex-container {
            width: 100%;
        }
        .photo-box {
            width: 120px;
            height: 160px;
            border: 1px solid #e2e8f0;
            background-color: #f7fafc;
            float: right;
            text-align: center;
            line-height: 160px;
            color: #cbd5e0;
            font-size: 10px;
            border-radius: 4px;
        }

        /* Data Table */
        .section-title {
            font-size: 10px;
            font-weight: 900;
            color: #2c5282;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            background-color: #ebf8ff;
            padding: 4px 10px;
            border-radius: 4px;
            clear: both;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table td {
            padding: 6px 0;
            border-bottom: 1px solid #f7fafc;
        }
        .label {
            font-weight: 600;
            width: 30%;
            color: #4a5568;
        }
        .separator {
            width: 3%;
            color: #a0aec0;
        }
        .value {
            color: #1a202c;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .sign-table {
            width: 100%;
        }
        .sign-table td {
            width: 50%;
            text-align: center;
        }
        .sign-label {
            margin-bottom: 60px;
            color: #4a5568;
        }
        .sign-name {
            font-weight: 800;
            text-decoration: underline;
        }
        .sign-nip {
            font-size: 9px;
            color: #718096;
        }

        /* Watermark Background */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: #f7fafc;
            z-index: -1;
            font-weight: 900;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="watermark">ORIGINAL</div>

    <div class="container">
        <div class="header">
            @if(isset($settings['school_logo']) && $settings['school_logo'])
                <img src="{{ public_path('storage/' . $settings['school_logo']) }}" class="school-logo">
            @else
                <div class="school-logo" style="background: #2c5282; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 24px;">
                    SA
                </div>
            @endif
            
            <div class="school-info">
                <h1 class="school-name">{{ $settings['school_name'] ?? 'SMA Islam Sultan Agung 1' }}</h1>
                <div class="school-details">
                    {{ $settings['school_address'] ?? 'Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242' }}<br>
                    Telp: {{ $settings['school_phone'] ?? '-' }} | Email: {{ $settings['school_email'] ?? '-' }}
                </div>
            </div>
        </div>

        <div class="doc-title">
            <h2>Biodata Peserta Didik</h2>
        </div>

        <div class="photo-box">
            PAS FOTO 3X4
        </div>

        <div class="section-title">Informasi Pribadi</div>
        <table class="data-table">
            <tr>
                <td class="label">Nama Lengkap</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->user->name }}</td>
            </tr>
            <tr>
                <td class="label">NIS / NISN</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->nis }} / {{ $student->nisn }}</td>
            </tr>
            <tr>
                <td class="label">Jenis Kelamin</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td class="label">Tempat, Tanggal Lahir</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->birth_place ?? '-' }}, {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d F Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Kelas Saat Ini</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->classroom->name ?? 'Belum Ditentukan' }}</td>
            </tr>
            <tr>
                <td class="label">Status Keaktifan</td>
                <td class="separator">:</td>
                <td class="value">AKTIF</td>
            </tr>
        </table>

        <div class="section-title">Informasi Keluarga</div>
        <table class="data-table">
            <tr>
                <td class="label">Nama Ayah Kandung</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->father_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nama Ibu Kandung</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->mother_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Alamat Rumah</td>
                <td class="separator">:</td>
                <td class="value">{{ $student->address ?? '-' }}</td>
            </tr>
        </table>

        <div class="section-title">Visi & Misi Sekolah</div>
        <div style="padding: 10px; background: #f7fafc; border-radius: 8px; font-style: italic; font-size: 10px; color: #4a5568;">
            <strong>Visi:</strong> {{ $settings['school_vision'] ?? '-' }}<br>
            <strong>Misi:</strong> {!! nl2br(e($settings['school_mission'] ?? '-')) !!}
        </div>

        <div class="footer">
            <table class="sign-table">
                <tr>
                    <td>
                        Mengetahui,<br>
                        Orang Tua / Wali Siswa
                        <div style="margin-top: 60px;">
                            ( .................................... )
                        </div>
                    </td>
                    <td>
                        {{ now()->translatedFormat('d F Y') }}<br>
                        Kepala Sekolah,
                        <div style="margin-top: 60px;">
                            <div class="sign-name">( {{ $settings['headmaster_name'] ?? 'Drs. H. Mulyadi, M.Pd' }} )</div>
                            <div class="sign-nip">NIP. {{ $settings['headmaster_nip'] ?? '19760512 200501 1 002' }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
