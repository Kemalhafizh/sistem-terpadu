<x-app-layout>
    <x-slot name="title">Rekening Tabungan Wadiah Siswa</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-teal-650 dark:text-teal-400 font-black">ADMINISTRASI KEUANGAN SYARIAH</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight mt-1">
                     Rekening Tabungan Wadiah Siswa
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola data rekening wadiah, batas jajan harian, serta aktivitas setor/tarik tunai siswa.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.finance.wadiah.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all flex items-center gap-2">
                     Dashboard Wadiah
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Search & Filter Card -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-6">
            <form action="{{ url()->current() }}" method="GET" class="flex flex-col md:flex-row items-center gap-4">
                <div class="relative w-full flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nomor rekening, NIS, atau nama siswa..." class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent text-gray-850 dark:text-white">
                </div>
                <div class="flex gap-2 w-full md:w-auto shrink-0">
                    <button type="submit" class="w-full md:w-auto bg-teal-650 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl py-3 px-6 text-xs font-bold transition-all shadow-md">
                        Cari Rekening
                    </button>
                    @if($search)
                        <a href="{{ route('admin.finance.wadiah.index') }}" class="w-full md:w-auto bg-gray-100 dark:bg-gray-700 hover:bg-gray-205 dark:hover:bg-gray-600 text-gray-700 dark:text-white rounded-2xl py-3 px-6 text-xs font-bold transition-all text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/55">
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Nomor Rekening</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Siswa</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Kelas</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-right">Saldo</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-right">Limit Harian</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-center">Status</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-750">
                        @forelse($accounts as $acc)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-5 font-mono font-bold text-sm text-teal-600 dark:text-teal-400">
                                    {{ $acc->account_number }}
                                </td>
                                <td class="py-5">
                                    <div class="font-bold text-sm text-gray-800 dark:text-white">
                                        {{ $acc->student->user->name ?? 'Tidak Terhubung' }}
                                    </div>
                                    <div class="text-[11px] font-semibold text-gray-400 mt-0.5">
                                        NIS: {{ $acc->student->nis ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-5">
                                    <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-extrabold text-[10px] rounded-lg">
                                        {{ $acc->student->classroom->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-5 font-black text-sm text-right text-gray-800 dark:text-white">
                                    Rp {{ number_format($acc->balance, 0, ',', '.') }}
                                </td>
                                <td class="py-5 font-black text-sm text-right text-gray-500 dark:text-gray-400">
                                    @if($acc->daily_spending_limit > 0)
                                        Rp {{ number_format($acc->daily_spending_limit, 0, ',', '.') }}
                                    @else
                                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-550 uppercase tracking-wide">Unlimited</span>
                                    @endif
                                </td>
                                <td class="py-5 text-center">
                                    @if($acc->is_active)
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-450 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.finance.wadiah.show', $acc->id) }}" class="p-2 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 hover:bg-teal-100 dark:hover:bg-teal-900/50 rounded-xl transition-all text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-xs" title="Lihat Detail Mutasi">
                                             Detail
                                        </a>
                                        @if($acc->is_active)
                                            <a href="{{ route('admin.finance.wadiah.deposit', $acc->id) }}" class="p-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-450 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-xl transition-all text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-xs" title="Setor Tunai">
                                                 Setor
                                            </a>
                                            <a href="{{ route('admin.finance.wadiah.withdraw', $acc->id) }}" class="p-2 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-450 hover:bg-rose-100 dark:hover:bg-rose-900/50 rounded-xl transition-all text-xs font-black uppercase tracking-wider flex items-center gap-1 shadow-xs" title="Tarik Tunai">
                                                 Tarik
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-xs text-gray-400 italic">
                                    Tidak ada rekening siswa yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accounts->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/55">
                    {{ $accounts->appends(['search' => $search])->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
