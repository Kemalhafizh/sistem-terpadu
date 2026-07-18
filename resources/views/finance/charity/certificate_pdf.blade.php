<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Donasi - {{ $donation->receipt_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 25px;
        }
        body {
            font-family: 'Georgia', 'Times New Roman', Times, serif;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .certificate-border {
            border: 6px double #0d9488; /* Double teal border */
            padding: 30px;
            height: 485px; /* Exact fit for A4 Landscape print */
            position: relative;
            background: #fafaf9; /* Warm stone-50 page feeling */
        }
        .certificate-inner {
            border: 1px solid #e2e8f0;
            padding: 20px;
            height: 440px;
            position: relative;
            background-color: #ffffff;
            box-shadow: inset 0 0 20px rgba(13, 148, 136, 0.03);
        }
        
        /* Kop Surat */
        .kop-surat {
            text-align: center;
            margin-bottom: 15px;
        }
        .kop-surat .yayasan {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #64748b;
            margin: 0;
            text-transform: uppercase;
        }
        .kop-surat .sekolah {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }
        .kop-surat .alamat {
            font-size: 9px;
            color: #64748b;
            margin: 0;
            font-style: italic;
        }
        .kop-line {
            border: none;
            border-top: 1px solid #0d9488;
            border-bottom: 2px solid #b45309; /* Gold accent line */
            height: 4px;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        /* Certificate Title */
        .title-container {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .certificate-title {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 22px;
            font-weight: bold;
            color: #0d9488;
            margin: 0;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        .certificate-subtitle {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            letter-spacing: 4px;
            color: #b45309; /* Gold */
            margin: 4px 0 0 0;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* Main Text Body */
        .message-container {
            text-align: center;
            padding: 0 45px;
            font-size: 13.5px;
            color: #334155;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .donor-name {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 3px;
            margin-top: 5px;
            margin-bottom: 5px;
            font-style: italic;
        }
        .highlight-text {
            font-weight: bold;
            color: #0d9488;
        }
        .campaign-title {
            font-weight: bold;
            font-style: italic;
            color: #0f172a;
        }

        /* Bottom Section: QR and Signatures */
        .bottom-container {
            position: absolute;
            bottom: 30px;
            left: 50px;
            right: 50px;
        }
        
        /* QR Code left */
        .qr-section {
            float: left;
            width: 260px;
        }
        .qr-placeholder {
            width: 58px;
            height: 58px;
            border: 2px dashed #0d9488;
            border-radius: 6px;
            padding: 3px;
            background: #ffffff;
            display: inline-block;
            vertical-align: middle;
        }
        .qr-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 4px;
            position: relative;
        }
        /* CSS Simulated QR Code Pixels */
        .qr-square-tl { position: absolute; top: 2px; left: 2px; width: 14px; height: 14px; border: 2px solid #0d9488; box-sizing: border-box; }
        .qr-square-tr { position: absolute; top: 2px; right: 2px; width: 14px; height: 14px; border: 2px solid #0d9488; box-sizing: border-box; }
        .qr-square-bl { position: absolute; bottom: 2px; left: 2px; width: 14px; height: 14px; border: 2px solid #0d9488; box-sizing: border-box; }
        .qr-center { position: absolute; top: 20px; left: 20px; width: 14px; height: 14px; background-color: #0d9488; border-radius: 2px; }
        
        .qr-description {
            display: inline-block;
            vertical-align: middle;
            font-size: 8.5px;
            color: #64748b;
            margin-left: 10px;
            width: 180px;
            line-height: 1.4;
        }
        .qr-description strong {
            color: #334155;
        }

        /* Signature block right */
        .signature-section {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature-date {
            font-size: 11px;
            color: #475569;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
            margin: 0 0 55px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .signature-name {
            font-size: 12.5px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-decoration: underline;
        }
        .signature-desc {
            font-size: 9.5px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .clear {
            clear: both;
        }
    </style>
</head>
<body>

<div class="certificate-border">
    <div class="certificate-inner">
        <!-- Kop Surat YBWSA / SMA -->
        <div class="kop-surat">
            <p class="yayasan">Yayasan Badan Wakaf Sultan Agung</p>
            <h1 class="sekolah">SMA Islam Sultan Agung 1 Semarang</h1>
            <p class="alamat">Jl. Mataram No.657, Wonodri, Kec. Semarang Selatan, Kota Semarang, Jawa Tengah 50242</p>
            <div class="kop-line"></div>
        </div>

        <!-- Title of Certificate -->
        <div class="title-container">
            <h2 class="certificate-title">Sertifikat Apresiasi Donasi Digital</h2>
            <div class="certificate-subtitle">Jazakumullah Khairan Katsiran</div>
        </div>

        <!-- Main text content -->
        <div class="message-container">
            <p>Diberikan kepada:</p>
            <span class="donor-name">
                {{ $donation->is_anonymous ? 'Hamba Allah' : ($donation->donor_name ?? $donation->user?->name) }}
            </span>
            <p style="margin-top: 6px;">
                atas donasi sebesar <span class="highlight-text">Rp {{ number_format($donation->amount, 0, ',', '.') }}</span> 
                pada program <span class="campaign-title">"{{ $donation->campaign?->title }}"</span> 
                pada tanggal <span class="highlight-text">{{ $donation->created_at->translatedFormat('d F Y') }}</span>.
            </p>
            <p style="font-size: 11px; color: #64748b; margin-top: 10px;">
                Semoga Allah Subhanahu Wa Ta'ala melipatgandakan pahala atas kebaikan yang diberikan dan menjadikannya amal jariyah abadi. Aamiin.
            </p>
        </div>

        <!-- Bottom Signature & Validation Area -->
        <div class="bottom-container">
            <!-- QR code validator block -->
            <div class="qr-section">
                <div class="qr-placeholder">
                    <div class="qr-inner">
                        <div class="qr-square-tl"></div>
                        <div class="qr-square-tr"></div>
                        <div class="qr-square-bl"></div>
                        <div class="qr-center"></div>
                    </div>
                </div>
                <div class="qr-description">
                    <strong>Resi Keuangan:</strong><br>
                    {{ $donation->receipt_number }}<br>
                    <span style="font-size: 7.5px;">Sertifikat digital sah terbit secara otomatis melalui Sistem Terpadu Keuangan YBWSA.</span>
                </div>
            </div>

            <!-- Signature block -->
            <div class="signature-section">
                <p class="signature-date">Semarang, {{ $donation->created_at->translatedFormat('d F Y') }}</p>
                <p class="signature-title">Kepala Sekolah,</p>
                <p class="signature-name">Dr. H. Tjoek Subhan Sulchan</p>
                <p class="signature-desc">SMA Islam Sultan Agung 1 Semarang</p>
            </div>
            
            <div class="clear"></div>
        </div>
    </div>
</div>

</body>
</html>
