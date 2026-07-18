<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-655 dark:text-teal-400">Audit Integritas Data</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight mt-1">
                    Audit Data Pra-Ekspor: {{ ucfirst($target) }} ({{ ucfirst($table === 'students' ? 'Siswa' : 'Guru') }})
                </h2>
            </div>
            <a href="{{ route('admin.integration.sync.dashboard') }}" 
               class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-750 transition-all duration-150 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    @php
        $issuesCollection = collect($issues);
        $errorCount = $issuesCollection->where('severity', 'error')->count();
        $warningCount = $issuesCollection->where('severity', 'warning')->count();
        $totalCount = $issuesCollection->count();
        $hasErrors = $errorCount > 0;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            {{-- Audit Overview Banners --}}
            @if($totalCount === 0)
                <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/10 border border-emerald-500/30 rounded-[2.5rem] p-8 shadow-md relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 bg-emerald-500/20 border border-emerald-500/40 rounded-3xl flex items-center justify-center text-3xl shrink-0 shadow-lg shadow-emerald-500/20">
                            ✅
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Hasil Audit Data</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Semua data lengkap dan valid. Siap diekspor!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                Jumlah Masalah Ditemukan: 0. Tidak terdeteksi kesalahan format atau kolom wajib yang kosong.
                            </p>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.integration.sync.export', [$target, $table]) }}" 
                       class="w-full md:w-auto px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider text-center transition-all duration-200 active:scale-95 shadow-lg shadow-teal-600/25 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Lanjutkan Ekspor JSON</span>
                    </a>
                </div>
            @else
                @if($hasErrors)
                    <div class="bg-gradient-to-r from-rose-500/10 via-red-500/10 to-rose-500/10 border-2 border-rose-500/30 rounded-[2.5rem] p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-rose-500/20 border border-rose-500/40 rounded-3xl flex items-center justify-center text-3xl animate-bounce shrink-0 shadow-lg shadow-rose-500/20">
                                ❌
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Jumlah Masalah Ditemukan: {{ $totalCount }}</span>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Audit Menemukan Masalah Kritis ({{ $errorCount }} Error)</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 max-w-2xl">
                                    Terdapat {{ $errorCount }} error data yang harus diperbaiki terlebih dahulu sebelum ekspor JSON dapat dilanjutkan. Warning bersifat opsional tetapi disarankan untuk diperbaiki.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-stretch md:items-end gap-2 w-full md:w-auto">
                            <button disabled 
                                    class="w-full md:w-auto px-6 py-3 bg-gray-300 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-2xl text-xs font-black uppercase tracking-wider cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Ekspor Dinonaktifkan</span>
                            </button>
                            <span class="text-[10px] text-rose-600 dark:text-rose-450 font-black uppercase tracking-widest text-center md:text-right block">Perbaiki semua ERROR untuk membuka kunci ekspor</span>
                        </div>
                    </div>
                @else
                    <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 border border-amber-500/30 rounded-[2.5rem] p-8 shadow-md relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-amber-500/20 border border-amber-500/40 rounded-3xl flex items-center justify-center text-3xl shrink-0 shadow-lg shadow-amber-500/20">
                                ⚠️
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400">Jumlah Masalah Ditemukan: {{ $totalCount }}</span>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Audit Menemukan Peringatan Non-Kritis ({{ $warningCount }} Warning)</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 max-w-2xl">
                                    Semua masalah berupa peringatan ringan. Anda diizinkan untuk mengabaikan peringatan ini dan melanjutkan ekspor data, namun disarankan untuk meninjau kembali.
                                </p>
                            </div>
                        </div>
                        
                        <a href="{{ route('admin.integration.sync.export', [$target, $table]) }}" 
                           class="w-full md:w-auto px-6 py-3 bg-teal-650 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider text-center transition-all duration-200 active:scale-95 shadow-lg shadow-teal-600/25 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Lanjutkan Ekspor JSON</span>
                        </a>
                    </div>
                @endif
            @endif

            {{-- Audit Issues Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Daftar Masalah Audit</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">Detail ketidaksesuaian data terhadap standar kementerian</p>
                </div>

                <div class="overflow-x-auto -mx-8 sm:-mx-8">
                    <div class="inline-block min-w-full align-middle px-8">
                        <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-3xl">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Nama Lengkap</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">ID / NIS / NIP</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Kolom Bermasalah</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Deskripsi Kesalahan</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-455 dark:text-gray-500">Tingkat Keparahan</th>
                                        <th scope="col" class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Aksi Perbaikan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @forelse($issues as $issue)
                                        <tr class="hover:bg-slate-50/55 dark:hover:bg-gray-950/20 transition-all duration-100">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $issue['name'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-450 font-medium">
                                                {{ $issue['identifier'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <code class="px-2 py-1 bg-slate-100 dark:bg-gray-900 rounded-lg text-xs font-mono text-teal-650 dark:text-teal-400 font-semibold">
                                                    {{ $issue['field'] }}
                                                </code>
                                            </td>
                                            <td class="px-6 py-4 text-xs text-gray-650 dark:text-gray-300 max-w-xs truncate font-medium">
                                                {{ $issue['issue'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($issue['severity'] === 'error')
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-350 border border-rose-100 dark:border-rose-900/30">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                        Error
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-350 border border-amber-100 dark:border-amber-900/30">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        Warning
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                                <a href="{{ $table === 'students' ? route('students.edit', $issue['id']) : route('users.edit', $issue['id']) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 border border-teal-500/30 hover:border-teal-500 hover:bg-teal-50/20 text-teal-600 dark:text-teal-400 rounded-xl font-bold transition-all">
                                                    <span>Perbaiki</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-xs text-gray-400 dark:text-gray-500 italic">
                                                Tidak ada masalah data yang ditemukan. Data Anda sepenuhnya bersih!
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
