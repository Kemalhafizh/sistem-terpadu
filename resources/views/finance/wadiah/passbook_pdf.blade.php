<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Tabungan Wadiah - {{ $account->account_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header-table {
            border-bottom: 3px double #000000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            width: 100%;
        }
        .header-title-main {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-title-sub {
            font-size: 19px;
            font-weight: bold;
            color: #0d9488;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 3px 0;
        }
        .header-address {
            font-size: 10px;
            color: #555555;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
            color: #1f2937;
        }
        .student-info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
            font-size: 11px;
        }
        .student-info-table td {
            padding: 4px 0;
        }
        .student-info-label {
            font-weight: bold;
            color: #4b5563;
        }
        .student-info-value {
            font-weight: bold;
            color: #111827;
        }
        .mutation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }
        .mutation-table th {
            background-color: #0d9488;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        .mutation-table td {
            padding: 8px 6px;
            border: 1px solid #e5e7eb;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge-setor {
            color: #0d9488;
            font-weight: bold;
        }
        .badge-tarik {
            color: #e11d48;
            font-weight: bold;
        }
        .badge-belanja {
            color: #4f46e5;
            font-weight: bold;
        }
        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
            width: 100%;
            font-size: 11px;
        }
        .signature-table {
            width: 100%;
        }
        .note-text {
            font-style: italic;
            color: #6b7280;
            font-size: 9px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Kop Surat -->
        <table class="header-table">
            <tr>
                <td align="center" style="line-height: 1.3;">
                    <span class="header-title-main">Yayasan Badan Wakaf Sultan Agung</span><br>
                    <span class="header-title-sub">SMA ISLAM SULTAN AGUNG 1 SEMARANG</span><br>
                    <span class="header-address">Jl. Mataram No.657, Semarang, Jawa Tengah. Telp: (024) 8313527 | Website: www.smaisla1.sch.id</span>
                </td>
            </tr>
        </table>

        <!-- Document Title -->
        <div class="doc-title">
            Laporan Mutasi Rekening Wadiah
        </div>

        <!-- Student Info Section -->
        <table class="student-info-table">
            <tr>
                <td width="15%" class="student-info-label">No. Rekening</td>
                <td width="3%">:</td>
                <td width="32%" class="student-info-value" style="font-family: monospace; font-size: 12px; color: #0d9488;">
                    {{ $account->account_number }}
                </td>
                <td width="15%" class="student-info-label">Nama Siswa</td>
                <td width="3%">:</td>
                <td width="32%" class="student-info-value">
                    {{ $account->student->user->name }}
                </td>
            </tr>
            <tr>
                <td class="student-info-label">NIS</td>
                <td>:</td>
                <td>{{ $account->student->nis }}</td>
                <td class="student-info-label">Kelas</td>
                <td>:</td>
                <td>{{ $account->student->classroom->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="student-info-label">Status</td>
                <td>:</td>
                <td style="font-weight: bold; color: {{ $account->is_active ? '#0d9488' : '#e11d48' }};">
                    {{ $account->is_active ? 'AKTIF' : 'NONAKTIF' }}
                </td>
                <td class="student-info-label">Tanggal Cetak</td>
                <td>:</td>
                <td>{{ now()->translatedFormat('d F Y H:i') }} WIB</td>
            </tr>
        </table>

        <!-- Mutation Table -->
        <table class="mutation-table">
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="18%">No. Referensi</th>
                    <th>Keterangan</th>
                    <th width="10%" class="text-center">Tipe</th>
                    <th width="14%" class="text-right">Debit (-)</th>
                    <th width="14%" class="text-right">Kredit (+)</th>
                    <th width="15%" class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $totalDebit = 0; 
                    $totalCredit = 0; 
                @endphp
                @forelse($transactions as $tx)
                    @php
                        $debit = 0;
                        $credit = 0;
                        if ($tx->type === 'deposit') {
                            $credit = $tx->amount;
                            $totalCredit += $credit;
                        } else {
                            $debit = $tx->amount;
                            $totalDebit += $debit;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-family: monospace;">{{ $tx->reference_number }}</td>
                        <td>{{ $tx->description ?? 'Transaksi Wadiah' }}</td>
                        <td class="text-center">
                            @if($tx->type === 'deposit')
                                <span class="badge-setor">SETOR</span>
                            @elseif($tx->type === 'withdrawal')
                                <span class="badge-tarik">TARIK</span>
                            @elseif($tx->type === 'purchase')
                                <span class="badge-belanja">KANTIN</span>
                            @else
                                <span style="font-weight: bold;">{{ strtoupper($tx->type) }}</span>
                            @endif
                        </td>
                        <td class="text-right" style="color: {{ $debit > 0 ? '#e11d48' : '#777777' }}; font-weight: {{ $debit > 0 ? 'bold' : 'normal' }};">
                            {{ $debit > 0 ? 'Rp ' . number_format($debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right" style="color: {{ $credit > 0 ? '#0d9488' : '#777777' }}; font-weight: {{ $credit > 0 ? 'bold' : 'normal' }};">
                            {{ $credit > 0 ? 'Rp ' . number_format($credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="text-right" style="font-weight: bold; color: #1f2937;">
                            Rp {{ number_format($tx->current_balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 15px; color: #6b7280; font-style: italic;">
                            Belum ada riwayat mutasi rekening.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right" style="padding: 8px;">Total Mutasi:</td>
                    <td class="text-right" style="color: #e11d48; padding: 8px;">
                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="color: #0d9488; padding: 8px;">
                        Rp {{ number_format($totalCredit, 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="background-color: #e5e7eb; padding: 8px; color: #111827;">
                        Rp {{ number_format($account->balance, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures & Notes -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td width="60%" valign="top">
                        <div class="note-text">
                            <strong>Catatan Keamanan:</strong><br>
                            1. Dokumen ini dicetak secara resmi melalui sistem informasi SISTA (Sistem Informasi Sekolah Terpadu).<br>
                            2. Simpan cetakan laporan mutasi ini sebagai bukti transaksi keuangan wadiah yang sah.<br>
                            3. Jika terdapat selisih pencatatan, harap segera melapor ke bagian Staf Keuangan SMA Islam Sultan Agung 1.
                        </div>
                    </td>
                    <td width="40%" align="right" valign="top">
                        <div style="text-align: center; display: inline-block;">
                            Semarang, {{ now()->translatedFormat('d F Y') }}<br>
                            <strong>Staf Keuangan SMA Islam Sultan Agung 1</strong>
                            <br><br><br><br>
                            ___________________________________<br>
                            Keuangan Syariah / Operator SISTA
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
