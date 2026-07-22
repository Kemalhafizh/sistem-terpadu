<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    Auditor Jurnal Keuangan Double-Entry & Fraud Detector
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Audit otomatis saldo debit-kredit, deteksi anomali pembukuan, dan kecurangan jurnal kas.</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('finance.audit.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Jalankan Audit Live
                    </button>
                </form>
                <a href="{{ route('finance.audit.export') }}" target="_blank" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs transition-all flex items-center gap-2 border border-gray-300 dark:border-gray-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Laporan Audit Resmi (PDF)
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="auditDashboard()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Health Score & Balance Overview Header -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Health Score Card -->
                <div class="lg:col-span-4 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col justify-between relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase text-gray-400 tracking-wider">Skor Integritas Keuangan</span>
                        <span class="text-[10px] font-mono font-bold text-gray-400" text="'Diperbarui: ' + auditReport.audited_at">Waktu Audit: {{ $auditReport['audited_at'] }}</span>
                    </div>

                    <div class="my-6 flex items-center justify-center gap-6">
                        <div class="relative w-28 h-28 flex items-center justify-center">
                            <!-- Ring background -->
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                <path class="text-gray-200 dark:text-gray-700" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                <path class="{{ $auditReport['health_score'] >= 90 ? 'text-emerald-500' : ($auditReport['health_score'] >= 60 ? 'text-amber-500' : 'text-rose-500') }}" stroke-dasharray="{{ $auditReport['health_score'] }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            </svg>
                            <div class="absolute flex flex-col items-center">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $auditReport['health_score'] }}%</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">SKOR</span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-bold text-gray-900 dark:text-white text-base">
                                @if($auditReport['health_score'] >= 90)
                                    Sangat Sehat & Balance
                                @elseif($auditReport['health_score'] >= 60)
                                    Perlu Perhatian Khusus
                                @else
                                    Terindikasi Anomali / Unbalanced
                                @endif
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total entri pembukuan: <strong class="text-gray-700 dark:text-gray-200">{{ number_format($auditReport['total_entries']) }} jurnal</strong></p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                        <span class="text-gray-500">Status Pembukuan:</span>
                        @if($auditReport['is_clean'])
                            <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full font-bold text-[10px] uppercase border border-emerald-500/20">Clean Audit</span>
                        @else
                            <span class="px-2.5 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full font-bold text-[10px] uppercase border border-rose-500/20">{{ $auditReport['findings_summary']['total_issues'] }} Temuan Anomali</span>
                        @endif
                    </div>
                </div>

                <!-- Global Balance Summary Card -->
                <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col justify-between space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase text-gray-900 dark:text-white tracking-wider">Pemeriksaan Keseimbangan Global (Global Ledger Balance)</h3>
                        @if($auditReport['global_balance']['is_balanced'])
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full font-black text-[10px] uppercase border border-emerald-500/20 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Double-Entry Perfect Balance
                            </span>
                        @else
                            <span class="px-3 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-full font-black text-[10px] uppercase border border-rose-500/20 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                Danger: Selisih Debit Kredit
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-750">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Total Akumulasi Debit</span>
                            <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($auditReport['global_balance']['total_debit'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-750">
                            <span class="text-[10px] font-bold uppercase text-gray-400">Total Akumulasi Kredit</span>
                            <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($auditReport['global_balance']['total_credit'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="p-4 rounded-2xl border {{ $auditReport['global_balance']['is_balanced'] ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/10 border-rose-500/20 text-rose-700 dark:text-rose-300' }}">
                            <span class="text-[10px] font-bold uppercase opacity-80">Selisih Global</span>
                            <h4 class="text-xl font-black mt-1">Rp {{ number_format($auditReport['global_balance']['difference'], 0, ',', '.') }}</h4>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: 100%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-500">Toleransi Selisih: Rp 0,00</span>
                    </div>
                </div>
            </div>

            <!-- Severity Counters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Jurnal Unbalanced (Kritis)</span>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($auditReport['findings_summary']['critical']) }} Jurnal</h3>
                    </div>
                    <div class="w-12 h-12 bg-rose-500/10 text-rose-500 rounded-2xl border border-rose-500/20 flex items-center justify-center font-black text-lg">
                        !
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Kas Yatim / Beban (Tinggi)</span>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($auditReport['findings_summary']['high']) }} Transaksi</h3>
                    </div>
                    <div class="w-12 h-12 bg-amber-500/10 text-amber-500 rounded-2xl border border-amber-500/20 flex items-center justify-center font-black text-lg">
                        
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-blue-500 uppercase tracking-wider">Anomali Nilai (Sedang)</span>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($auditReport['findings_summary']['medium']) }} Jurnal</h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/10 text-blue-500 rounded-2xl border border-blue-500/20 flex items-center justify-center font-black text-lg">
                        ℹ
                    </div>
                </div>
            </div>

            <!-- Findings Tables Section -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Daftar Temuan Audit Jurnal Pembukuan
                    </h3>
                </div>

                <!-- Unbalanced Entries Table -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black uppercase text-rose-500 tracking-wider">1. Jurnal Tidak Seimbang (Unbalanced Journal Entries)</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="p-3 rounded-l-xl">ID / Tanggal</th>
                                    <th class="p-3">Deskripsi & Ref</th>
                                    <th class="p-3 text-right">Total Debit</th>
                                    <th class="p-3 text-right">Total Kredit</th>
                                    <th class="p-3 text-right">Selisih</th>
                                    <th class="p-3 text-center rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                                @forelse($auditReport['unbalanced_entries'] as $entry)
                                    <tr class="hover:bg-rose-500/5 transition-colors">
                                        <td class="p-3 font-mono font-bold text-gray-900 dark:text-white">
                                            #{{ $entry->id }}<br>
                                            <span class="text-[10px] text-gray-400 font-normal">{{ $entry->entry_date }}</span>
                                        </td>
                                        <td class="p-3 text-gray-800 dark:text-gray-200">
                                            {{ $entry->description }}<br>
                                            <span class="text-[10px] font-mono text-gray-400">Ref: {{ $entry->reference ?? '-' }}</span>
                                        </td>
                                        <td class="p-3 text-right font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($entry->total_debit, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($entry->total_credit, 0, ',', '.') }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-rose-500">Rp {{ number_format($entry->imbalance_amount, 0, ',', '.') }}</td>
                                        <td class="p-3 text-center">
                                            <button @click="inspectJournal({{ $entry->id }})" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500 hover:text-white rounded-xl font-bold text-[10px] uppercase transition-all">
                                                Inspeksi Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-6 text-center text-gray-400 italic">Tidak ditemukan jurnal selisih (Seluruh entri jurnal seimbang).</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Unpaired Cash Transactions Table -->
                <div class="space-y-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <h4 class="text-xs font-black uppercase text-amber-500 tracking-wider">2. Transaksi Tanpa Akun Kas/Bank Berpasangan (Unpaired Cash/Asset)</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase text-[10px] font-bold">
                                <tr>
                                    <th class="p-3 rounded-l-xl">ID / Tanggal</th>
                                    <th class="p-3">Deskripsi Jurnal</th>
                                    <th class="p-3">Keterangan Catatan Audit</th>
                                    <th class="p-3 text-center rounded-r-xl">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                                @forelse($auditReport['unpaired_cash_entries'] as $entry)
                                    <tr class="hover:bg-amber-500/5 transition-colors">
                                        <td class="p-3 font-mono font-bold text-gray-900 dark:text-white">
                                            #{{ $entry->id }}<br>
                                            <span class="text-[10px] text-gray-400 font-normal">{{ $entry->entry_date }}</span>
                                        </td>
                                        <td class="p-3 text-gray-800 dark:text-gray-200">
                                            {{ $entry->description }}
                                        </td>
                                        <td class="p-3 text-amber-600 dark:text-amber-400 font-medium">
                                            {{ $entry->issue_description }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <button @click="inspectJournal({{ $entry->id }})" class="px-3 py-1.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500 hover:text-white rounded-xl font-bold text-[10px] uppercase transition-all">
                                                Tinjau Jurnal
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-gray-400 italic">Seluruh transaksi pengeluaran/beban memiliki akun kas/bank berpasangan yang valid.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <!-- Detail Modal Inspection -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition.scale class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-200 dark:border-gray-700">
                    <div class="bg-gray-900 text-white p-6 flex items-center justify-between border-b border-gray-800">
                        <div>
                            <h3 class="text-base font-black tracking-tight" x-text="'Inspeksi Detail Jurnal #' + (activeJournal ? activeJournal.id : '')"></h3>
                            <p class="text-xs text-gray-400 mt-0.5" x-text="activeJournal ? activeJournal.description : ''"></p>
                        </div>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <template x-if="activeJournal">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 dark:bg-gray-900/50 p-4 rounded-2xl border border-gray-200 dark:border-gray-750">
                                    <div>
                                        <span class="text-gray-400 uppercase text-[10px] font-bold block">Tanggal Jurnal</span>
                                        <strong class="text-gray-900 dark:text-white" x-text="activeJournal.entry_date"></strong>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 uppercase text-[10px] font-bold block">Nomor Referensi</span>
                                        <strong class="text-gray-900 dark:text-white" x-text="activeJournal.reference || '-'"></strong>
                                    </div>
                                </div>

                                <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Rincian Baris Debit & Kredit (Journal Lines)</h4>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold uppercase text-[10px]">
                                            <tr>
                                                <th class="p-2.5">Kode / Akun</th>
                                                <th class="p-2.5 text-right">Debit</th>
                                                <th class="p-2.5 text-right">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 font-medium">
                                            <template x-for="line in activeJournal.lines" :key="line.account_code">
                                                <tr>
                                                    <td class="p-2.5">
                                                        <strong class="text-gray-900 dark:text-white font-mono" x-text="line.account_code"></strong> - <span x-text="line.account_name"></span>
                                                    </td>
                                                    <td class="p-2.5 text-right font-mono font-bold text-gray-900 dark:text-white" x-text="'Rp ' + line.debit.toLocaleString('id-ID')"></td>
                                                    <td class="p-2.5 text-right font-mono font-bold text-gray-900 dark:text-white" x-text="'Rp ' + line.credit.toLocaleString('id-ID')"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                        <tfoot class="border-t-2 border-gray-300 dark:border-gray-600 font-bold bg-gray-50 dark:bg-gray-900/50">
                                            <tr>
                                                <td class="p-2.5 uppercase text-gray-500 text-[10px]">Total Baris Jurnal</td>
                                                <td class="p-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + activeJournal.total_debit.toLocaleString('id-ID')"></td>
                                                <td class="p-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + activeJournal.total_credit.toLocaleString('id-ID')"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function auditDashboard() {
            return {
                modalOpen: false,
                activeJournal: null,

                inspectJournal(id) {
                    fetch("{{ url('/finance/audit/journal') }}/" + id)
                        .then(res => res.json())
                        .then(data => {
                            this.activeJournal = data;
                            this.modalOpen = true;
                        });
                }
            };
        }
    </script>
</x-app-layout>
