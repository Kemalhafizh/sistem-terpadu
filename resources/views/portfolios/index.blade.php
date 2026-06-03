<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl">📚</span>
                    <span>{{ __('Direktori e-Portfolio Siswa') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Monitoring keaktifan berkas siswa, verifikasi dokumen sertifikat lomba harian, serta penganugerahan lencana kehormatan sekolah
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Glassmorphic Dashboard Panel --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm overflow-hidden">
            <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest block mb-4">Daftar Siswa Sekolah</span>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-slate-800 text-[10px] font-black uppercase tracking-widest text-gray-400">
                            <th class="pb-3 pt-1 pl-4">Siswa / NIS</th>
                            <th class="pb-3 pt-1">Kelas</th>
                            <th class="pb-3 pt-1 text-center">Jumlah Portofolio</th>
                            <th class="pb-3 pt-1 text-center">Lencana Kehormatan</th>
                            <th class="pb-3 pt-1 pr-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-800/50">
                        @forelse($students as $st)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-800/20 transition duration-150">
                                <td class="py-4 pl-4 flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center font-black text-xs shrink-0">
                                        {{ strtoupper(substr($st->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="block font-black text-gray-800 dark:text-gray-200 leading-tight">{{ $st->name }}</span>
                                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-0.5">NISN: {{ $st->nisn }}</span>
                                    </div>
                                </td>
                                <td class="py-4 text-gray-700 dark:text-gray-300 font-bold">
                                    {{ $st->classroom->name ?? 'Belum Diatur' }}
                                </td>
                                <td class="py-4 text-center">
                                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-black">
                                        {{ $st->portfolio_items_count }} berkas
                                    </span>
                                </td>
                                <td class="py-4 text-center">
                                    @if($st->badges_count > 0)
                                        <span class="px-2.5 py-1 bg-amber-500/10 text-amber-600 rounded-lg text-xs font-black flex items-center justify-center space-x-1 w-max mx-auto border border-amber-500/20">
                                            <span>🏆</span>
                                            <span>{{ $st->badges_count }} Lencana</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-600 font-semibold">-</span>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-right">
                                    <a href="{{ route('portfolios.review', $st) }}"
                                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-indigo-500/10">
                                        🔍 Ulas & Lencana
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400 dark:text-gray-600 font-semibold">
                                    Tidak ditemukan data siswa aktif di sekolah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
