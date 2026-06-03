<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>RPP - {{ $lessonPlan->topic }}</title>
    <style>
        /* ============================================= */
        /* BASE STYLES — DomPDF Compatible               */
        /* ============================================= */
        @page {
            size: A4 portrait;
            margin: 2cm 1.5cm 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #1a1a1a;
            line-height: 1.6;
        }

        /* ============================================= */
        /* LETTERHEAD                                     */
        /* ============================================= */
        .letterhead-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .letterhead-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 80px;
            text-align: center;
            padding-right: 12px;
        }

        .logo-img {
            width: 65px;
            height: auto;
        }

        .school-info {
            text-align: center;
            padding-left: 8px;
        }

        .school-foundation {
            font-size: 10pt;
            color: #374151;
            letter-spacing: 0.5px;
        }

        .school-name {
            font-size: 16pt;
            font-weight: bold;
            color: #065f46;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .school-address {
            font-size: 8.5pt;
            color: #6b7280;
            margin-top: 3px;
        }

        .school-contact {
            font-size: 8.5pt;
            color: #6b7280;
        }

        /* Divider Line */
        .header-divider {
            border: none;
            border-top: 3px double #065f46;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* ============================================= */
        /* DOCUMENT TITLE                                 */
        /* ============================================= */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h1 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #065f46;
            margin-bottom: 3px;
        }

        .doc-subtitle {
            font-size: 11pt;
            color: #374151;
            font-weight: bold;
        }

        /* ============================================= */
        /* METADATA TABLE                                 */
        /* ============================================= */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 10.5pt;
        }

        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
            border: none;
        }

        .meta-label {
            font-weight: bold;
            width: 150px;
        }

        .meta-colon {
            width: 15px;
            text-align: center;
        }

        /* ============================================= */
        /* RPP SECTIONS                                   */
        /* ============================================= */
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #065f46;
            margin-top: 20px;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1.5px solid #065f46;
            text-transform: uppercase;
        }

        .section-content {
            font-size: 10.5pt;
            text-align: justify;
            margin-bottom: 15px;
            white-space: pre-line;
            padding-left: 5px;
        }

        /* ============================================= */
        /* SIGNATURE AREA                                 */
        /* ============================================= */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 45px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .sig-role {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }

        .sig-line {
            margin-top: 70px;
            border-bottom: 1px solid #1a1a1a;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }

        .sig-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1a1a1a;
            margin-top: 5px;
        }

        .sig-nip {
            font-size: 9pt;
            color: #6b7280;
        }

        /* ============================================= */
        /* FOOTER                                         */
        /* ============================================= */
        .print-date {
            font-size: 8.5pt;
            color: #9ca3af;
            text-align: right;
            margin-top: 40px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>

    <!-- ============================================= -->
    <!-- LETTERHEAD                                     -->
    <!-- ============================================= -->
    <table class="letterhead-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('images/logo-sekolah.png') }}" class="logo-img" alt="Logo" onerror="this.style.display='none'">
            </td>
            <td class="school-info">
                <div class="school-foundation">YAYASAN BADAN PENDIDIKAN DAN SOSIAL SULTAN AGUNG</div>
                <div class="school-name">SMA ISLAM SULTAN AGUNG 1 SEMARANG</div>
                <div class="school-address">Jl. Mataram No. 657, Wonodri, Kec. Semarang Selatan, Kota Semarang, Jawa Tengah 50242</div>
                <div class="school-contact">Telp: (024) 8316532 | Email: smaisa1_smg@yahoo.co.id | Website: www.smaisa1semarang.sch.id</div>
            </td>
        </tr>
    </table>

    <hr class="header-divider">

    <!-- ============================================= -->
    <!-- DOCUMENT TITLE                                 -->
    <!-- ============================================= -->
    <div class="doc-title">
        <h1>Rencana Pelaksanaan Pembelajaran (RPP)</h1>
        <div class="doc-subtitle">Kurikulum Digital — Pertemuan/Minggu Ke-{{ $lessonPlan->week_number }}</div>
    </div>

    <!-- ============================================= -->
    <!-- METADATA INFORMATION                           -->
    <!-- ============================================= -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Mata Pelajaran</td>
            <td class="meta-colon">:</td>
            <td><strong>{{ $lessonPlan->subject->name }}</strong></td>
            
            <td class="meta-label" style="padding-left: 30px;">Tahun Akademik</td>
            <td class="meta-colon">:</td>
            <td>{{ $lessonPlan->academicYear->year ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kelas / Rombel</td>
            <td class="meta-colon">:</td>
            <td>{{ $lessonPlan->classroom->name }}</td>
            
            <td class="meta-label" style="padding-left: 30px;">Guru Pengampu</td>
            <td class="meta-colon">:</td>
            <td>{{ $lessonPlan->user->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Topik Utama</td>
            <td class="meta-colon">:</td>
            <td colspan="4"><strong>{{ $lessonPlan->topic }}</strong></td>
        </tr>
    </table>

    <!-- ============================================= -->
    <!-- RPP CONTENT DETAILS                            -->
    <!-- ============================================= -->

    <!-- I. Standar Kompetensi -->
    <div class="section-title">I. Standar Kompetensi / Kompetensi Inti & Dasar (KI - KD)</div>
    <div class="section-content">{{ $lessonPlan->competency_standards ?: '— Tidak ditentukan —' }}</div>

    <!-- II. Tujuan Pembelajaran -->
    <div class="section-title">II. Tujuan Pembelajaran</div>
    <div class="section-content">{{ $lessonPlan->learning_objectives }}</div>

    <!-- III. Kegiatan Pembelajaran -->
    <div class="section-title">III. Langkah & Kegiatan Pembelajaran</div>
    <div class="section-content">{{ $lessonPlan->learning_activities }}</div>

    <!-- IV. Metode Penilaian -->
    <div class="section-title">IV. Metode Penilaian & Asesmen</div>
    <div class="section-content">{{ $lessonPlan->assessment_method ?: '— Tidak ditentukan —' }}</div>

    <!-- V. Sumber & Media Belajar -->
    <div class="section-title">V. Sumber & Media Pembelajaran</div>
    <div class="section-content">{{ $lessonPlan->resources ?: '— Tidak ditentukan —' }}</div>

    <!-- ============================================= -->
    <!-- SIGNATURE AREA                                 -->
    <!-- ============================================= -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-role">Guru Pengampu</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $lessonPlan->user->name }}</div>
                <div class="sig-nip">NIP. ____________________</div>
            </td>
            <td>
                <div class="sig-role">Waka Kurikulum</div>
                <div class="sig-line"></div>
                <div class="sig-name">
                    @if($lessonPlan->status === 'approved' && $lessonPlan->reviewer)
                        {{ $lessonPlan->reviewer->name }}
                    @else
                        ____________________
                    @endif
                </div>
                <div class="sig-nip">NIP. ____________________</div>
            </td>
        </tr>
    </table>

    <!-- ============================================= -->
    <!-- PRINT DATE & STATUS                            -->
    <!-- ============================================= -->
    <div class="print-date">
        Status RPP: {{ strtoupper($lessonPlan->status) }} | Dicetak pada: {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y — HH:mm') }} WIB
    </div>

</body>
</html>
