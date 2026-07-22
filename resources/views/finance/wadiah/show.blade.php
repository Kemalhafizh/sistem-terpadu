<x-app-layout>
    <x-slot name="title">Mutasi Rekening Wadiah - {{ $account->account_number }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-teal-600 dark:text-teal-400 font-black">MUTASI TABUNGAN SYARIAH</span>
                <h2 class="font-black text-3xl text-gray-805 dark:text-white leading-tight mt-1">
                     Detail Rekening & Mutasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Detail informasi rekening tabungan wadiah {{ $account->student->user->name }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.finance.wadiah.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all flex items-center gap-2">
                    ← Daftar Rekening
                </a>
                @if($account->is_active)
                    <a href="{{ route('admin.finance.wadiah.deposit', $account->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all shadow-md shadow-emerald-500/10 flex items-center gap-2">
                         Setor Tunai
                    </a>
                    <a href="{{ route('admin.finance.wadiah.withdraw', $account->id) }}" class="bg-rose-600 hover:bg-rose-700 text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all shadow-md shadow-rose-500/10 flex items-center gap-2">
                         Tarik Tunai
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Side: Student & Account Cards -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Account Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-teal-500/10 dark:bg-teal-500/5 rounded-full blur-2xl"></div>
                
                <div class="flex flex-col items-center text-center pb-6 border-b border-gray-100 dark:border-gray-750">
                    <div class="w-20 h-20 bg-teal-50 dark:bg-teal-900/30 text-teal-650 dark:text-teal-400 rounded-[2rem] flex items-center justify-center text-4xl shadow-inner mb-4">
                        
                    </div>
                    <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest mb-1">PROFIL TABUNGAN</span>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white leading-snug">{{ $account->student->user->name }}</h3>
                    <p class="text-xs text-gray-400 font-semibold mt-1">NIS: {{ $account->student->nis }}</p>
                    <span class="mt-3 px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-extrabold text-[10px] rounded-lg">
                        Kelas: {{ $account->student->classroom->name ?? '-' }}
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold">Nomor Rekening:</span>
                        <span class="font-mono font-bold text-sm text-teal-600 dark:text-teal-400">{{ $account->account_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold">Status Rekening:</span>
                        @if($account->is_active)
                            <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                Aktif
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-450 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-xs text-gray-450 font-bold">Saldo Tersedia:</span>
                        <span class="font-black text-2xl text-teal-600 dark:text-teal-400">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Daily spending Limit Form Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8">
                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">PENGATURAN KARTU</span>
                <h3 class="font-black text-lg text-gray-855 dark:text-white mb-4">Limit Belanja Harian</h3>
                
                @if($account->daily_spending_limit > 0)
                    <div class="p-4 bg-teal-50/50 dark:bg-teal-900/10 border border-teal-100/50 dark:border-teal-900/20 rounded-2xl mb-6">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Limit Belanja Hari Ini:</span>
                            <span class="font-bold text-xs text-teal-650 dark:text-teal-400">Rp {{ number_format($account->daily_spending_limit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sisa Batas Jajan:</span>
                            <span class="font-bold text-xs text-teal-650 dark:text-teal-400">Rp {{ number_format($account->remaining_daily_limit, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl mb-6">
                        <p class="text-xs text-gray-450 italic leading-relaxed text-center">
                            Tidak ada batasan belanja harian (Unlimited). Siswa bebas berbelanja menggunakan kartu.
                        </p>
                    </div>
                @endif

                <form action="{{ route('admin.finance.wadiah.limit.store', $account->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="daily_spending_limit" class="block text-[10px] font-black text-gray-450 dark:text-gray-450 uppercase tracking-widest mb-2">Perbarui Limit Harian (Rp)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-sm font-bold text-gray-400">Rp</span>
                            <input type="number" id="daily_spending_limit" name="daily_spending_limit" value="{{ old('daily_spending_limit', round($account->daily_spending_limit)) }}" min="0" step="1000" class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-bold text-gray-800 dark:text-white focus:ring-2 focus:ring-teal-500 focus:outline-none" required>
                        </div>
                        <p class="text-[9px] text-gray-400 mt-2 font-semibold leading-relaxed">
                            * Isi dengan <span class="font-bold">0</span> untuk menonaktifkan batasan limit belanja harian.
                        </p>
                    </div>
                    <button type="submit" class="w-full bg-teal-650 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl py-3 px-6 text-xs font-black uppercase tracking-wider transition-all shadow-md">
                        Simpan Batasan Limit
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Side: Transactions History Statement -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest block">RIWAYAT MUTASI REKENING</span>
                        <h3 class="font-black text-xl text-gray-800 dark:text-white mt-1"> Aktivitas Transaksi</h3>
                    </div>
                    <a href="{{ route('admin.finance.wadiah.export-passbook', $account->id) }}" class="bg-teal-50 hover:bg-teal-100 dark:bg-teal-900/20 dark:hover:bg-teal-900/35 text-teal-650 text-teal-600 dark:text-teal-400 rounded-2xl py-2.5 px-4 text-xs font-bold transition-all flex items-center gap-2 border border-teal-100/50 dark:border-teal-900/30 shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Unduh Mutasi (PDF)
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/55">
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Tanggal & Waktu</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">No. Referensi</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Tipe</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-right">Jumlah</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-right">Saldo Akhir</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Keterangan</th>
                                <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-750">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-4 text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        {{ $tx->created_at->translatedFormat('d M Y H:i') }}
                                    </td>
                                    <td class="py-4 font-mono text-[11px] font-bold text-gray-450 dark:text-gray-400">
                                        {{ $tx->reference_number }}
                                    </td>
                                    <td class="py-4">
                                        @if($tx->type === 'deposit')
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-450 font-extrabold text-[9px] rounded-lg uppercase tracking-wider">
                                                Setor
                                            </span>
                                        @elseif($tx->type === 'withdrawal')
                                            <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-455 font-extrabold text-[9px] rounded-lg uppercase tracking-wider">
                                                Tarik
                                            </span>
                                        @elseif($tx->type === 'purchase')
                                            <span class="px-2.5 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 font-extrabold text-[9px] rounded-lg uppercase tracking-wider">
                                                Kantin
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-450 font-extrabold text-[9px] rounded-lg uppercase tracking-wider">
                                                {{ $tx->type }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 font-black text-sm text-right @if($tx->type === 'deposit') text-emerald-600 dark:text-emerald-400 @else text-gray-800 dark:text-white @endif">
                                        @if($tx->type === 'deposit') + @else - @endif Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 font-black text-sm text-right text-gray-500 dark:text-gray-400">
                                        Rp {{ number_format($tx->current_balance, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 text-xs text-gray-500 dark:text-gray-300 max-w-[150px] truncate" title="{{ $tx->description }}">
                                        {{ $tx->description ?? '-' }}
                                    </td>
                                    <td class="py-4 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                        {{ $tx->processedBy->name ?? 'Merchant / Sistem' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-xs text-gray-400 italic">
                                        Belum ada riwayat mutasi rekening wadiah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/55">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
