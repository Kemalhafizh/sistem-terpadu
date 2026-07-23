<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-gray-100 leading-tight uppercase tracking-tight flex items-center">
                    <a href="{{ route('import-export.index') }}" class="mr-3 hover:text-blue-500 transition-colors" title="Kembali ke Hub">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    Riwayat Import & Rollback Hub
                </h2>
                <p class="text-xs font-semibold text-gray-400 mt-1 uppercase tracking-wider">
                    Pantau log audit batch dan pulihkan data terimpor secara aman
                </p>
            </div>
            <div>
                <a href="{{ route('import-export.index') }}" 
                   class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl text-xs font-bold uppercase tracking-widest shadow-lg shadow-blue-500/10 transition-all flex items-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Impor / Ekspor Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-gray-700 shadow-2xl p-6 md:p-8 overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-wider">
                            <th class="pb-3 text-center w-12">#</th>
                            <th class="pb-3">Modul</th>
                            <th class="pb-3">Nama Berkas</th>
                            <th class="pb-3">Oleh</th>
                            <th class="pb-3 text-center">Metrik Baris</th>
                            <th class="pb-3 text-center">Status</th>
                            <th class="pb-3">Tanggal</th>
                            <th class="pb-3 text-center">Aksi Pemulihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs font-semibold text-gray-700 dark:text-gray-200">
                        @forelse($logs as $index => $log)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors">
                                <td class="py-4 text-center text-gray-400 font-bold">{{ $logs->firstItem() + $index }}</td>
                                <td class="py-4">
                                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                        {{ $log->module }}
                                    </span>
                                </td>
                                <td class="py-4 font-bold text-gray-800 dark:text-gray-100 max-w-[180px] truncate" title="{{ $log->file_name }}">
                                    {{ $log->file_name }}
                                </td>
                                <td class="py-4">{{ $log->importedBy->name }}</td>
                                <td class="py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2 text-[10px]">
                                        <span class="text-gray-400" title="Total Baris">Σ {{ $log->total_rows }}</span>
                                        <span class="text-emerald-500 font-bold" title="Sukses"> {{ $log->success_count }}</span>
                                        @if($log->error_count > 0)
                                            <span class="text-red-500 font-bold" title="Error"> {{ $log->error_count }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 text-center">
                                    @if($log->status === 'completed')
                                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-200/50">
                                            Ran
                                        </span>
                                    @elseif($log->status === 'rolled_back')
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700/60 text-gray-500 dark:text-gray-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-gray-200/50">
                                            Rolled Back
                                        </span>
                                    @elseif($log->status === 'failed')
                                        <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-red-200/50">
                                            Failed
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-blue-200/50 animate-pulse">
                                            Running
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2" x-data="{ openErrors: false }">
                                        <!-- Error Diagnostics Toggle -->
                                        @if(!empty($log->error_details))
                                            <button @click="openErrors = !openErrors" 
                                                    class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 hover:bg-amber-100 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all border border-amber-200/30"
                                                    title="Lihat Detail Error"
                                            >
                                                Logs Error
                                            </button>

                                            <!-- Errors Modal / Collapsible -->
                                            <div x-show="openErrors" 
                                                 @click.away="openErrors = false"
                                                 class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                                                 style="display: none;"
                                            >
                                                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 max-w-lg w-full p-6 shadow-2xl text-left max-h-[80vh] overflow-y-auto">
                                                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-4 mb-4">
                                                        <h3 class="text-sm font-black text-gray-800 dark:text-gray-100 uppercase tracking-widest">
                                                            Detail Error Validasi Berkas
                                                        </h3>
                                                        <button @click="openErrors = false" class="text-gray-400 hover:text-gray-500">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </div>
                                                    
                                                    <div class="space-y-4">
                                                        @foreach($log->error_details as $rowNum => $errorsList)
                                                            <div class="border border-red-100 dark:border-red-900/40 bg-red-50/20 dark:bg-red-950/20 rounded-xl p-3">
                                                                <h5 class="text-[10px] font-black text-red-600 dark:text-red-400 uppercase">Baris File Ke-{{ $rowNum }}</h5>
                                                                <ul class="list-disc list-inside mt-1.5 space-y-1">
                                                                    @foreach($errorsList as $err)
                                                                        <li class="text-[10px] font-semibold text-gray-600 dark:text-gray-300 leading-normal">{{ $err }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Rollback Trigger -->
                                        @if($log->status === 'completed')
                                            <form action="{{ route('import-export.rollback', $log->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/rollback seluruh data batch dari file ini? Semua siswa/karyawan baru yang terdaftar akan dihapus secara permanen.')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-[9px] font-black uppercase tracking-wider transition-all shadow-md shadow-red-500/10">
                                                    Rollback
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-wider">Tidak Aktif</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400 dark:text-gray-500 italic">
                                    <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Belum ada riwayat impor yang tercatat di sistem
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
