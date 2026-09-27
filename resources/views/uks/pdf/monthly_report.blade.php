<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan UKS - {{ $month }}/{{ $year }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
        }
        
        /* Kop Surat */
        .kop-surat {
            border-bottom: 3px double #000000;
            padding-bottom: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .kop-instansi {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .kop-sekolah {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0d9488;
            margin: 3px 0;
        }
        .kop-alamat {
            font-size: 9px;
            color: #555555;
            margin: 0;
            font-style: italic;
        }

        /* Laporan Title */
        .report-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        /* Tables styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            color: #1f2937;
            font-weight: bold;
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-ringan { background-color: #ccfbf1; color: #0f766e; }
        .badge-sedang { background-color: #fef3c7; color: #b45309; }
        .badge-berat { background-color: #fee2e2; color: #b91c1c; }

        .badge-kembali { background-color: #dcfce7; color: #15803d; }
        .badge-istirahat { background-color: #ccfbf1; color: #0f766e; }
        .badge-pulang { background-color: #dbeafe; color: #1d4ed8; }
        .badge-rujuk { background-color: #fef2f2; color: #991b1b; }

        /* Signature block */
        .signature-container {
            margin-top: 40px;
            width: 100%;
        }
        .signature-box {
            width: 45%;
            display: inline-block;
            vertical-align: top;
        }
        .signature-box-right {
            text-align: right;
        }
        .signature-space {
            height: 60px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #0f766e;
            border-bottom: 1px solid #0f766e;
            padding-bottom: 3px;
            margin-bottom: 10px;
            margin-top: 15px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <div class="kop-instansi">{{ school_foundation_name(false) }}</div>
        <div class="kop-sekolah">{{ school_name() }}</div>
        <div class="kop-alamat">
            {{ school_contact_line() }}
        </div>
    </div>

    <!-- Title -->
    <div class="report-title">
        Laporan Bulanan Unit Kesehatan Sekolah (UKS)<br>
        Bulan: {{ \Carbon\Carbon::create(null, $month, 1)->translatedFormat('F') }} {{ $year }}
    </div>

    <!-- 1. RINGKASAN STATISTIK -->
    <div class="section-header">1. Ringkasan Statistik Bulanan</div>
    
    <table style="width: 100%; border: none;">
        <tr style="border: none;">
            <td style="width: 48%; border: none; padding: 0 10px 0 0;">
                <!-- Tingkat Gejala -->
                <table style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th colspan="2">Distribusi Tingkat Keparahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gejala Ringan</td>
                            <td class="text-right font-bold">{{ $severitySummary['ringan'] }} Siswa</td>
                        </tr>
                        <tr>
                            <td>Gejala Sedang</td>
                            <td class="text-right font-bold">{{ $severitySummary['sedang'] }} Siswa</td>
                        </tr>
                        <tr>
                            <td>Kasus Berat / Darurat</td>
                            <td class="text-right font-bold text-red">{{ $severitySummary['berat'] }} Siswa</td>
                        </tr>
                        <tr style="background-color: #f9fafb;">
                            <td class="font-bold">Total Kunjungan</td>
                            <td class="text-right font-bold">{{ $visits->count() }} Siswa</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            
            <td style="width: 48%; border: none; padding: 0 0 0 10px;">
                <!-- Tindakan / Disposisi -->
                <table style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th colspan="2">Distribusi Tindakan / Disposisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Diizinkan Kembali ke Kelas</td>
                            <td class="text-right font-bold">{{ $dispositionSummary['kembali_kelas'] }} Kali</td>
                        </tr>
                        <tr>
                            <td>Istirahat di Ruang UKS</td>
                            <td class="text-right font-bold">{{ $dispositionSummary['istirahat_uks'] }} Kali</td>
                        </tr>
                        <tr>
                            <td>Dipulangkan / Dijemput Ortu</td>
                            <td class="text-right font-bold">{{ $dispositionSummary['pulang'] }} Kali</td>
                        </tr>
                        <tr>
                            <td>Dirujuk ke Rumah Sakit</td>
                            <td class="text-right font-bold">{{ $dispositionSummary['rujuk_rs'] }} Kali</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border: none; margin-top: 0;">
        <tr style="border: none;">
            <td style="width: 48%; border: none; padding: 0 10px 0 0;">
                <!-- Top Complaints -->
                <table>
                    <thead>
                        <tr>
                            <th>Top 5 Keluhan Utama Siswa</th>
                            <th class="text-right" style="width: 80px;">Frekuensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topComplaints as $complaint => $count)
                            <tr>
                                <td>"{{ $complaint }}"</td>
                                <td class="text-right font-bold">{{ $count }} Kali</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center italic color-gray">Tidak ada data keluhan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            
            <td style="width: 48%; border: none; padding: 0 0 0 10px;">
                <!-- Top Medicines -->
                <table>
                    <thead>
                        <tr>
                            <th>Pemakaian Obat Terbanyak</th>
                            <th class="text-right" style="width: 80px;">Kuantitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicineUsage as $usage)
                            <tr>
                                <td>{{ $usage->name }}</td>
                                <td class="text-right font-bold">{{ $usage->total_used }} Pcs</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center italic color-gray">Tidak ada pemakaian obat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- 2. DETAIL LOG KUNJUNGAN -->
    <div class="section-header" style="page-break-before: always;">2. Log Detail Kunjungan Medis Bulanan</div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Tanggal/Jam</th>
                <th style="width: 110px;">Siswa (Kelas)</th>
                <th>Keluhan & Diagnosis</th>
                <th style="width: 60px;" class="text-center">Severity</th>
                <th style="width: 80px;" class="text-center">Tindakan</th>
                <th style="width: 80px;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($visits as $visit)
                <tr>
                    <td class="text-center font-bold">
                        {{ $visit->visit_time->translatedFormat('d-m-Y') }}<br>
                        <span style="font-size: 8px; color: #777777; font-weight: normal;">
                            {{ $visit->visit_time->translatedFormat('H:i') }} WIB
                        </span>
                    </td>
                    <td>
                        <span class="font-bold">{{ $visit->student->user->name ?? '-' }}</span><br>
                        <span style="font-size: 8px; color: #555555;">
                            {{ $visit->student->classroom->name ?? '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="font-bold" style="color: #0f766e;">"{{ $visit->complaint }}"</span>
                        @if($visit->diagnosis)
                            <div style="font-size: 9px; color: #555555; margin-top: 3px;">
                                <strong>Diag:</strong> {{ $visit->diagnosis }}
                            </div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($visit->severity === 'berat')
                            <span class="badge badge-berat">Berat</span>
                        @elseif($visit->severity === 'sedang')
                            <span class="badge badge-sedang">Sedang</span>
                        @else
                            <span class="badge badge-ringan">Ringan</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $dispBadges = [
                                'kembali_kelas' => 'badge-kembali',
                                'istirahat_uks' => 'badge-istirahat',
                                'pulang' => 'badge-pulang',
                                'rujuk_rs' => 'badge-rujuk'
                            ];
                            $dispNames = [
                                'kembali_kelas' => 'Kembali Kelas',
                                'istirahat_uks' => 'Istirahat UKS',
                                'pulang' => 'Dipulangkan',
                                'rujuk_rs' => 'Rujuk RS'
                            ];
                            $badgeCls = $dispBadges[$visit->disposition] ?? '';
                            $nameText = $dispNames[$visit->disposition] ?? 'Istirahat';
                        @endphp
                        <span class="badge {{ $badgeCls }}">{{ $nameText }}</span>
                    </td>
                    <td>
                        {{ $visit->handler->name ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center italic" style="padding: 20px;">
                        Tidak ada log kunjungan siswa sakit pada bulan ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SIGNATURE BLOCK -->
    <div class="signature-container">
        <div class="signature-box" style="float: left;">
            Mengetahui,<br>
            Kepala Sekolah {{ school_short_name() }}<br>
            <div class="signature-space"></div>
            <span class="signature-name">{{ signature_name(school_principal_name()) }}</span><br>
            {{ signature_nip(school_principal_nip()) }}
        </div>
        
        <div class="signature-box" style="float: right; text-align: right;">
            Semarang, {{ now()->translatedFormat('d F Y') }}<br>
            Petugas Unit Kesehatan Sekolah (UKS)<br>
            <div class="signature-space"></div>
            <span class="signature-name">{{ auth()->user()->name }}</span><br>
            NIP. PTK-UKS-{{ auth()->id() }}
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
