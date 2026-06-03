<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Survei - {{ $survey->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: normal;
            letter-spacing: 0.5px;
        }
        .header h1 {
            margin: 3px 0;
            font-size: 18px;
            font-weight: bold;
            color: #059669; /* Calm Emerald green */
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .meta-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .meta-info td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
        }
        .meta-info td.label {
            font-weight: bold;
            color: #475569;
            width: 25%;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4px;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-data th, .table-data td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: left;
        }
        .table-data th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        .scale-stats {
            margin-bottom: 10px;
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 8px 12px;
            border-radius: 4px;
        }
        .scale-stats span {
            margin-right: 25px;
            font-weight: bold;
            color: #065f46;
        }

        .text-responses {
            margin: 0;
            padding-left: 20px;
        }
        .text-responses li {
            margin-bottom: 4px;
            color: #334155;
        }

        .footer-section {
            width: 100%;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            text-align: center;
            vertical-align: top;
        }
        .signature-space {
            height: 60px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- Official Header -->
    <div class="header">
        <h2>YAYASAN BADAN WAKAF SULTAN AGUNG</h2>
        <h1>SMA ISLAM SULTAN AGUNG 1 SEMARANG</h1>
        <p>Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242</p>
    </div>

    <div class="doc-title">LAPORAN HASIL ANALISIS SURVEI DIGITAL</div>

    <!-- Survey Meta Info -->
    <table class="meta-info">
        <tr>
            <td class="label">Judul Survei</td>
            <td>{{ $survey->title }}</td>
            <td class="label">Status Survei</td>
            <td class="text-bold" style="color: {{ $survey->status === 'active' ? '#059669' : ($survey->status === 'closed' ? '#ef4444' : '#64748b') }};">
                {{ strtoupper($survey->status) }}
            </td>
        </tr>
        <tr>
            <td class="label">Deskripsi</td>
            <td colspan="3">{{ $survey->description ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Pelaksanaan</td>
            <td>
                {{ $survey->start_date ? $survey->start_date->format('d M Y H:i') : '-' }} s/d 
                {{ $survey->end_date ? $survey->end_date->format('d M Y H:i') : '-' }}
            </td>
            <td class="label">Sifat Respons</td>
            <td>{{ $survey->is_anonymous ? 'Anonim (PDP Protected)' : 'Identifikasi Terbuka' }}</td>
        </tr>
        <tr>
            <td class="label">Target Sasaran</td>
            <td>
                Role: {{ $survey->target_role ? ucfirst($survey->target_role) : 'Semua Stakeholder' }}
                @if($survey->classroom)
                    <br>Kelas: {{ $survey->classroom->name }}
                @endif
            </td>
            <td class="label">Partisipasi Respons</td>
            <td class="text-bold">
                {{ $survey->responses()->count() }} Jawaban / {{ $responseRate }}% Rate
            </td>
        </tr>
    </table>

    <div class="section-title">A. ANALISIS PERTANYAAN SURVEI</div>

    @foreach($analytics as $index => $qAnalytics)
        <div style="margin-bottom: 20px; page-break-inside: avoid;">
            <h4 style="font-size: 11px; margin: 10px 0 5px 0; color: #1e293b;">
                Pertanyaan {{ $loop->iteration }}: {{ $qAnalytics['question_text'] }} 
                <span style="font-weight: normal; color: #64748b;">(Tipe: {{ ucfirst($qAnalytics['type']) }}, {{ $qAnalytics['response_count'] }} respons)</span>
            </h4>

            @if(in_array($qAnalytics['type'], ['radio', 'select', 'checkbox']))
                <table class="table-data">
                    <thead>
                        <tr>
                            <th width="60%">Pilihan Jawaban</th>
                            <th width="20%" class="text-center">Jumlah Pemilih</th>
                            <th width="20%" class="text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($qAnalytics['data'] as $optionData)
                            <tr>
                                <td>{{ $optionData['label'] }}</td>
                                <td class="text-center">{{ $optionData['count'] }}</td>
                                <td class="text-center">{{ $optionData['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif($qAnalytics['type'] === 'scale')
                <div class="scale-stats">
                    <span>Nilai Rata-rata (Mean): {{ $qAnalytics['data']['average'] }}</span>
                    <span>Nilai Tengah (Median): {{ $qAnalytics['data']['median'] }}</span>
                </div>
                <table class="table-data">
                    <thead>
                        <tr>
                            <th width="30%" class="text-center">Skor</th>
                            <th width="40%">Keterangan</th>
                            <th width="15%" class="text-center">Jumlah Pemilih</th>
                            <th width="15%" class="text-center">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($qAnalytics['data']['distribution'] as $score => $dist)
                            <tr>
                                <td class="text-center text-bold">{{ $score }}</td>
                                <td>
                                    @if($score == $survey->questions->where('id', $qAnalytics['question_id'])->first()->scale_min)
                                        {{ $qAnalytics['data']['min_label'] }}
                                    @elseif($score == $survey->questions->where('id', $qAnalytics['question_id'])->first()->scale_max)
                                        {{ $qAnalytics['data']['max_label'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">{{ $dist['count'] }}</td>
                                <td class="text-center">{{ $dist['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data distribusi untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(in_array($qAnalytics['type'], ['text', 'textarea', 'date']))
                <ul class="text-responses">
                    @forelse($qAnalytics['data'] as $textResponse)
                        <li>"{{ $textResponse }}"</li>
                    @empty
                        <li style="list-style-type: none; color: #64748b;">- Belum ada respons esai tertulis -</li>
                    @endforelse
                </ul>
            @endif
        </div>
    @endforeach

    <!-- Signatures -->
    <div class="footer-section">
        <div class="signature-box" style="float: left;">
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <div class="signature-space"></div>
            <p><strong>Kepala Sekolah SMA Islam Sultan Agung 1</strong></p>
        </div>
        
        <div class="signature-box" style="float: right;">
            <p>Semarang, {{ date('d F Y') }}<br>Penanggung Jawab Laporan</p>
            <div class="signature-space"></div>
            <p><strong>Waka Humas / Kurikulum</strong></p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
