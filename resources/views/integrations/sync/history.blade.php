<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-700 dark:text-teal-400">Log & Audit Trace</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight mt-1">
                    Riwayat Sinkronisasi Eksternal
                </h2>
            </div>
            <a href="{{ route('admin.integration.sync.dashboard') }}" 
               class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150 flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard Integrasi</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, activeLog: null, activeTarget: '', activeTable: '' }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">

            {{-- History Logs Table Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="pb-6 border-b border-gray-100 dark:border-gray-700 mb-6">
                    <h3 class="text-xl font-black text-gray-900 dark:text-white">Log Aktivitas Sinkronisasi</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">Riwayat lengkap audit ekspor kementerian</p>
                </div>

                <div class="overflow-x-auto -mx-8 sm:-mx-8">
                    <div class="inline-block min-w-full align-middle px-8">
                        <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-3xl">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Sistem Target</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Tabel Lokal</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Waktu Eksekusi</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Total Diproses</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Sukses</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Gagal</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Operator</th>
                                        <th scope="col" class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-500">Log Audit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @forelse($reports as $report)
                                        <tr class="hover:bg-slate-50/55 dark:hover:bg-gray-950/20 transition-all duration-100">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($report->system_target === 'dapodik')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-100 dark:border-blue-900/30">
                                                        Dapodik
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/30">
                                                        EMIS
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($report->local_table === 'students')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-100 dark:border-purple-900/30">
                                                        Siswa
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-100 dark:border-amber-900/30">
                                                        Guru
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                {{ $report->sync_run_at ? $report->sync_run_at->timezone('Asia/Jakarta')->format('d M Y H:i') : $report->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                <span class="text-[10px] text-gray-400 block mt-0.5">{{ $report->created_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $report->records_processed }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-1 rounded-lg">
                                                    {{ $report->records_success }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($report->records_failed > 0)
                                                    <span class="text-xs font-bold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/20 px-2.5 py-1 rounded-lg animate-pulse">
                                                        {{ $report->records_failed }}
                                                    </span>
                                                @else
                                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-600 px-2.5 py-1">
                                                        0
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-300 font-semibold">
                                                {{ $report->runBy->name ?? 'System' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                                <button @click="activeLog = {{ json_encode($report->error_logs) }}; activeTarget = '{{ $report->system_target }}'; activeTable = '{{ $report->local_table }}'; showModal = true" 
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 border border-teal-500/30 hover:border-teal-500 hover:bg-teal-50/20 text-teal-600 dark:text-teal-400 rounded-xl font-bold transition-all">
                                                    <span>Detail Log</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-xs text-gray-400 dark:text-gray-500 italic">
                                                Belum ada riwayat aktivitas sinkronisasi.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-8">
                    {{ $reports->links() }}
                </div>
            </div>

        </div>

        {{-- Detail Log Modal (Alpine.js powered) --}}
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="display: none;">
            
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>
            
            <!-- Modal Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl border border-gray-100 dark:border-gray-700 max-w-3xl w-full relative z-10 max-h-[85vh] flex flex-col">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700 mb-4">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Audit Trace Log</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mt-0.5">
                            Detail Log Audit: <span class="capitalize" x-text="activeTarget"></span> (<span x-text="activeTable === 'students' ? 'Siswa' : 'Guru'"></span>)
                        </h3>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-1.5 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                
                <div class="overflow-y-auto flex-1 space-y-4 pr-2 py-2">
                    <!-- Empty logs condition -->
                    <div x-show="!activeLog || activeLog.length === 0" class="py-16 text-center text-xs text-gray-400 dark:text-gray-500 italic">
                        Tidak ada log error yang terdeteksi. Semua data sukses diproses tanpa kendala!
                    </div>
                    
                    <!-- Logs list -->
                    <template x-if="activeLog && activeLog.length > 0">
                        <div class="space-y-3">
                            <template x-for="(item, index) in activeLog" :key="index">
                                <div class="p-4 bg-slate-50 dark:bg-gray-900/50 border border-slate-100 dark:border-gray-800 rounded-3xl">
                                    <div class="flex justify-between items-start gap-3">
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500 mt-0.5 font-semibold" x-text="item.identifier"></p>
                                        </div>
                                        <span :class="item.severity === 'error' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-100 dark:border-rose-900/30' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border-amber-100 dark:border-amber-900/30'"
                                              class="px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md border"
                                              x-text="item.severity">
                                        </span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs border-t border-gray-200/40 dark:border-gray-700/50 pt-2">
                                        <div>
                                            <span class="font-bold text-teal-700 dark:text-teal-400">Kolom Bermasalah:</span> 
                                            <code class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-800 rounded text-[10px] font-semibold ml-1" x-text="item.field"></code>
                                        </div>
                                        <div>
                                            <span class="font-bold text-rose-700 dark:text-rose-400">Masalah:</span> 
                                            <span class="text-gray-600 dark:text-gray-300 font-medium ml-1" x-text="item.issue"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 mt-4 flex justify-end">
                    <button @click="showModal = false" class="px-6 py-2.5 bg-teal-700 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Tutup Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
