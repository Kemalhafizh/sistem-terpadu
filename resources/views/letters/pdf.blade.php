<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $letter->letter_number ?? 'Draf Surat' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 2cm 2cm 2cm 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 80px;
            padding-bottom: 10px;
            vertical-align: middle;
            text-align: center;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
            padding-bottom: 10px;
        }
        .header-text h2 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-text h1 {
            margin: 2px 0 0 0;
            font-size: 16pt;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-text p {
            margin: 2px 0 0 0;
            font-size: 9pt;
            font-style: italic;
            line-height: 1.2;
        }
        .letter-meta {
            text-align: center;
            margin-bottom: 25px;
        }
        .letter-title {
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        .letter-number {
            font-size: 11pt;
            margin: 4px 0 0 0;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .footer-sign {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .footer-sign td {
            width: 50%;
            vertical-align: bottom;
        }
        .sign-placeholder {
            height: 100px;
            position: relative;
        }
        .signature-img {
            max-height: 80px;
            position: absolute;
            top: 5px;
            left: 20px;
            z-index: 10;
        }
        .signer-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .verification-box {
            border: 1px solid #888;
            padding: 8px;
            margin-right: 18px;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.35;
        }
        .verification-box strong {
            display: block;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <!-- Kop Surat -->
    @if($template->header_html)
        {!! $template->header_html !!}
    @else
        <table class="header-table">
            <tr>
                <td class="header-logo">
                    <img src="data:image/png;base64,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" alt="Logo {{ school_short_name() }}" class="header-logo">
                </td>
                <td class="header-text">
                    <h2>{{ school_foundation_name(false) }}</h2>
                    <h1>{{ school_short_name() }}</h1>
                    @if(school_accreditation())<p>Terakreditasi {{ school_accreditation() }}</p>@endif
                    <p>{{ school_contact_line() }}</p>
                </td>
            </tr>
        </table>
    @endif

    <!-- Metadata Surat -->
    <div class="letter-meta">
        <h3 class="letter-title">{{ $template->name }}</h3>
        <p class="letter-number">Nomor: {{ $letter->letter_number ?? '..........................................' }}</p>
    </div>

    <!-- Isi Surat -->
    <div class="content">
        {!! $renderedBody !!}
    </div>

    <!-- Tanda Tangan & Penutup -->
    <table class="footer-sign">
        <tr>
            <td>
                @if(!empty($verificationUrl))
                    <div class="verification-box">
                        <strong>Verifikasi Keabsahan Surat</strong>
                        No. Surat: {{ $letter->letter_number ?? '-' }}<br>
                        Kode Verifikasi: {{ $letter->verification_token }}<br>
                        Tautan: {{ $verificationUrl }}
                    </div>
                @endif
            </td>
            <td>
                <p>Semarang, {{ $letter->issue_date->translatedFormat('d F Y') }}</p>
                <p>{{ $template->signer_title ?? 'Kepala Sekolah' }},</p>

                <div class="sign-placeholder">
                    @if($signatureBase64)
                        <img src="{{ $signatureBase64 }}" class="signature-img" alt="Tanda Tangan">
                    @endif
                </div>

                @php
                    $resolvedPrincipal = \App\Models\PositionAssignment::holderOn('kepala_sekolah', $letter->issue_date)?->name ?: school_principal_name();
                    $signerName = $template->signer_name ?: ($letter->approver?->name ?: $resolvedPrincipal);
                    $signerIsPrincipal = blank($template->signer_name) || $template->signer_name === school_principal_name();
                @endphp
                <p class="signer-name">{{ signature_name($signerName) }}</p>
                <p>{{ signature_nip($signerIsPrincipal ? school_principal_nip() : null) }}</p>
            </td>
        </tr>
    </table>
</body>
</html>
