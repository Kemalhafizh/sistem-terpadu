<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Resmi Audit Keuangan Double-Entry - SISTA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: #ffffff !important; padding: 0 !important; }
            .page-container { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="p-6 sm:p-10">

    <!-- Top Action Bar (Hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-8 bg-white p-5 rounded-3xl shadow-lg border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-black text-slate-900 tracking-tight">Laporan Resmi Hasil Audit Pembukuan Keuangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Dokumen resmi pemeriksaan otomatis integritas jurnal pembukuan berpasangan.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Laporan PDF
            </button>
            <a href="{{ route('finance.audit.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <!-- Official Report Page Container -->
    <div class="page-container max-w-4xl mx-auto bg-white p-10 rounded-3xl border border-slate-200 shadow-xl space-y-8">
        <!-- Formal Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-6 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-black uppercase tracking-widest text-emerald-700">YAYASAN BADAN WAKAF SULTAN AGUNG (YBWSA)</span>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">SMA ISLAM SULTAN AGUNG 1 SEMARANG</h2>
                <p class="text-xs text-slate-500">Jl. Mataram No. 657 Semarang | Telp: (024) 8314941 | Sistem Terpadu (SISTA)</p>
            </div>
            <div class="text-right space-y-1">
                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-800 rounded-lg text-[10px] font-mono font-bold uppercase border border-slate-300">DOKUMEN INTERAL AUDIT</span>
                <p class="text-[10px] text-slate-400 font-mono">TGL AUDIT: {{ $auditReport['audited_at'] }}</p>
            </div>
        </div>

        <!-- Document Title -->
        <div class="text-center space-y-1">
            <h3 class="text-lg font-black uppercase text-slate-900 tracking-wide underline">LAPORAN HASIL AUDIT INTEGRITAS PEMBUKUAN DOUBLE-ENTRY</h3>
            <p class="text-xs text-slate-500">Periode Pemeriksaan Pembukuan Keuangan Tahun Ajaran Aktif</p>
        </div>

        <!-- Audit Executive Summary Table -->
        <div class="space-y-3">
            <h4 class="text-xs font-black uppercase text-slate-900 tracking-wider">I. Ringkasan Hasil Pemeriksaan (Executive Summary)</h4>
            <table class="w-full text-xs border border-slate-300 text-left">
                <tbody class="divide-y divide-slate-200 font-medium">
                    <tr class="bg-slate-50">
                        <td class="p-3 font-bold text-slate-700 w-1/3">Skor Integritas Keuangan</td>
                        <td class="p-3 font-bold font-mono text-slate-900">
                            <span class="px-2 py-0.5 rounded text-white text-[11px] {{ $auditReport['health_score'] >= 90 ? 'bg-emerald-600' : 'bg-rose-600' }}">
                                {{ $auditReport['health_score'] }}% / 100%
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-slate-700">Total Akumulasi Debit Global</td>
                        <td class="p-3 font-mono font-bold text-slate-900">Rp {{ number_format($auditReport['global_balance']['total_debit'], 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-bold text-slate-700">Total Akumulasi Kredit Global</td>
                        <td class="p-3 font-mono font-bold text-slate-900">Rp {{ number_format($auditReport['global_balance']['total_credit'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-slate-700">Status Keseimbangan (Balance)</td>
                        <td class="p-3 font-bold text-slate-900">
                            @if($auditReport['global_balance']['is_balanced'])
                                <span class="text-emerald-700 uppercase font-black font-mono">SEIMBANG (SEMPURNA SELISIH Rp 0)</span>
                            @else
                                <span class="text-rose-600 uppercase font-black font-mono">TIDAK SEIMBANG (SELISIH Rp {{ number_format($auditReport['global_balance']['difference'], 0, ',', '.') }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg-slate-50">
                        <td class="p-3 font-bold text-slate-700">Total Entri Jurnal Diperiksa</td>
                        <td class="p-3 font-mono text-slate-900">{{ number_format($auditReport['total_entries']) }} Entri Jurnal</td>
                    </tr>
                    <tr>
                        <td class="p-3 font-bold text-slate-700">Total Temuan Anomali/Selisih</td>
                        <td class="p-3 font-bold text-slate-900">
                            {{ number_format($auditReport['findings_summary']['total_issues']) }} Catatan Temuan 
                            (<span class="text-rose-600">{{ $auditReport['findings_summary']['critical'] }} Kritis</span>, 
                            <span class="text-amber-600">{{ $auditReport['findings_summary']['high'] }} Tinggi</span>, 
                            <span class="text-blue-600">{{ $auditReport['findings_summary']['medium'] }} Sedang</span>)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Detailed Findings Section -->
        <div class="space-y-3 pt-4">
            <h4 class="text-xs font-black uppercase text-slate-900 tracking-wider">II. Detail Catatan Jurnal Bermasalah / Selisih</h4>
            <table class="w-full text-xs border border-slate-300 text-left">
                <thead class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px]">
                    <tr>
                        <th class="p-2.5 border-b border-slate-300">ID Jurnal</th>
                        <th class="p-2.5 border-b border-slate-300">Tanggal</th>
                        <th class="p-2.5 border-b border-slate-300">Keterangan Jurnal</th>
                        <th class="p-2.5 border-b border-slate-300 text-right">Selisih</th>
                        <th class="p-2.5 border-b border-slate-300">Tingkat Anomali</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium">
                    @forelse($auditReport['unbalanced_entries'] as $entry)
                        <tr>
                            <td class="p-2.5 font-mono font-bold">#{{ $entry->id }}</td>
                            <td class="p-2.5 font-mono">{{ $entry->entry_date }}</td>
                            <td class="p-2.5">{{ $entry->description }}</td>
                            <td class="p-2.5 text-right font-mono text-rose-600 font-bold">Rp {{ number_format($entry->imbalance_amount, 0, ',', '.') }}</td>
                            <td class="p-2.5 font-bold text-rose-600 uppercase text-[10px]">Unbalanced (Kritis)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-4 text-center text-slate-400 italic">Tidak ada catatan jurnal selisih yang ditemukan. Pembukuan 100% seimbang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Formal Signatures -->
        <div class="pt-12 grid grid-cols-2 gap-8 text-center text-xs">
            <div class="space-y-16">
                <div>
                    <p class="font-bold text-slate-700">Petugas Auditor / Staf Keuangan,</p>
                </div>
                <div>
                    <p class="font-black text-slate-900 underline">{{ auth()->user()->name ?? 'Staf Keuangan SISTA' }}</p>
                    <p class="text-[10px] text-slate-400 font-mono">NIP: 198503122010011002</p>
                </div>
            </div>
            <div class="space-y-16">
                <div>
                    <p class="font-bold text-slate-700">Mengetahui,<br>Kepala Sekolah / Ketua YBWSA</p>
                </div>
                <div>
                    <p class="font-black text-slate-900 underline">Drs. H. Ahmad Dahlan, M.Pd.</p>
                    <p class="text-[10px] text-slate-400 font-mono">NIP: 196807151994031005</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
