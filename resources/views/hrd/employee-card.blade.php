<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Pegawai - {{ $employee->user->name }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            -webkit-print-color-adjust: exact;
        }
        .card {
            width: 241px;
            height: 380px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 15px;
            box-sizing: border-box;
            position: relative;
            text-align: center;
            border: 2px solid #1d4ed8;
        }
        .logo-section {
            margin-top: 5px;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .school-name {
            font-size: 7.5pt;
            opacity: 0.9;
            margin-top: 2px;
        }
        .divider {
            width: 100%;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 10px 0;
        }
        .card-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #f59e0b;
        }
        .photo-box {
            width: 90px;
            height: 110px;
            background-color: #ffffff;
            border: 2px solid #f59e0b;
            border-radius: 6px;
            margin: 15px auto;
            display: block;
            position: relative;
        }
        .photo-placeholder {
            font-size: 35pt;
            line-height: 110px;
            color: #1e3a8a;
        }
        .info-section {
            margin-top: 10px;
        }
        .name {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.2;
            word-wrap: break-word;
        }
        .position {
            font-size: 8pt;
            opacity: 0.85;
            margin-top: 3px;
            font-style: italic;
        }
        .niy {
            font-family: monospace;
            font-size: 8pt;
            color: #f59e0b;
            margin-top: 5px;
            font-weight: bold;
        }
        .footer-logo {
            position: absolute;
            bottom: 12px;
            left: 0;
            right: 0;
            font-size: 6pt;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    <div class="card">
        <!-- Header -->
        <div class="logo-section">YBWSA</div>
        <div class="school-name">SMA Islam Sultan Agung 1</div>
        
        <div class="divider"></div>
        <div class="card-title">Kartu Pegawai</div>
        
        <!-- Foto -->
        <div class="photo-box">
            <div class="photo-placeholder"></div>
        </div>

        <!-- Info Pegawai -->
        <div class="info-section">
            <div class="name">{{ $employee->user->name }}</div>
            <div class="position">{{ $employee->position }}</div>
            <div class="niy">NIY: {{ $employee->employee_number }}</div>
        </div>

        <!-- Footer -->
        <div class="footer-logo">
            Sistem Informasi Sekolah Terpadu (SISTA)
        </div>
    </div>

</body>
</html>
