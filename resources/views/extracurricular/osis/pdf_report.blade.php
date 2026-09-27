<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Kas OSIS</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 80px;
            text-align: center;
        }
        .header-text {
            text-align: center;
        }
        .header-text h1 {
            font-size: 16px;
            margin: 0 0 3px 0;
            color: #047857; /* Emerald Primary */
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-text h2 {
            font-size: 14px;
            margin: 0 0 5px 0;
            color: #111827;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-text p {
            font-size: 9px;
            margin: 0;
            color: #6b7280;
            font-style: italic;
        }
        .divider {
            border-top: 2px solid #047857;
            border-bottom: 0.5px solid #047857;
            height: 3px;
            margin-bottom: 20px;
        }
        .title-section {
            text-align: center;
            margin-bottom: 25px;
        }
        .title-section h3 {
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .title-section p {
            font-size: 10px;
            margin: 0;
            color: #4b5563;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .summary-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
            border-radius: 8px;
        }
        .summary-card-emerald {
            background-color: #ecfdf5;
            border: 1.5px solid #a7f3d0;
            padding: 12px;
            text-align: left;
            border-radius: 8px;
        }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        .summary-value {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }
        .summary-value-emerald {
            font-size: 15px;
            font-weight: bold;
            color: #047857;
        }
        .summary-value-red {
            font-size: 15px;
            font-weight: bold;
            color: #b91c1c;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .ledger-table th {
            background-color: #047857;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #047857;
            text-align: left;
        }
        .ledger-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            color: #374151;
        }
        .ledger-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .badge-income {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-expense {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-income {
            color: #059669;
            font-weight: bold;
        }
        .text-expense {
            color: #dc2626;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-title {
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 50px;
        }
        .signature-name {
            font-size: 10px;
            font-weight: bold;
            color: #111827;
            text-decoration: underline;
        }
        .signature-nip {
            font-size: 9px;
            color: #6b7280;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Khas {{ school_short_name() }} -->
    <table class="header-table">
        <tr>
            <!-- Placeholder logo space to keep layout, using high contrast typography and layout -->
            <td class="header-text">
                <h1>{{ school_foundation_name(false) }}</h1>
                <h2>{{ school_name() }}</h2>
                <p>{{ school_contact_line(['address', 'phone']) }}</p>
                <p>{{ school_contact_line(['website', 'email']) }}</p>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Judul Laporan -->
    <div class="title-section">
        <h3>Laporan Pertanggungjawaban Keuangan Kas OSIS</h3>
        <p>Rentang Periode Pencatatan: {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}</p>
    </div>

    <!-- 3 Kolom Ringkasan Finansial -->
    <table class="summary-table">
        <tr>
            <td style="width: 31%; padding-right: 10px;">
                <div class="summary-card">
                    <div class="summary-label">Total Pemasukan</div>
                    <div class="summary-value-emerald">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 31%; padding-right: 10px; padding-left: 5px;">
                <div class="summary-card">
                    <div class="summary-label">Total Pengeluaran</div>
                    <div class="summary-value-red">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 38%; padding-left: 10px;">
                <div class="summary-card-emerald">
                    <div class="summary-label">Saldo Kas OSIS Akhir</div>
                    <div class="summary-value" style="color: {{ $netBalance >= 0 ? '#047857' : '#b91c1c' }}">
                        Rp {{ number_format($netBalance, 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Jurnal Buku Besar -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 28%;">Deskripsi Transaksi</th>
                <th style="width: 15%; text-align: right;">Nominal</th>
                <th style="width: 10%; text-align: center;">Petugas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $trx->transaction_date->translatedFormat('d M Y') }}</td>
                    <td>
                        @if($trx->type === 'income')
                            <span class="badge badge-income">Masuk</span>
                        @else
                            <span class="badge badge-expense">Keluar</span>
                        @endif
                    </td>
                    <td>{{ $trx->category }}</td>
                    <td class="font-bold">{{ $trx->description }}</td>
                    <td class="text-right {{ $trx->type === 'income' ? 'text-income' : 'text-expense' }}">
                        {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td class="text-center" style="font-size: 8px; color: #6b7280;">{{ $trx->recorder->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #6b7280;">
                        Tidak ditemukan data pencatatan transaksi kas OSIS pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <table class="signature-table">
        <tr>
            <td style="width: 60%;"></td>
            <td style="width: 40%; text-align: left;">
                <p style="margin: 0 0 5px 0; font-size: 10px; color: #4b5563;">Semarang, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="signature-title" style="margin: 0 0 45px 0; font-weight: bold; text-transform: uppercase;">
                    Mengetahui,<br>
                    Waka Kesiswaan {{ school_short_name() }}
                </p>
                <p class="signature-name" style="margin: 0;">{{ signature_name(null) }}</p>
                <p class="signature-nip" style="margin: 0;">{{ signature_nip(null) }}</p>
            </td>
        </tr>
    </table>

</body>
</html>
