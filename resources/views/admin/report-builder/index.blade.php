<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-chart-pie text-indigo-400 mr-2"></i> {{ __('Custom Report Builder') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.report-builder.create') }}" id="btn-create-report" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg shadow-indigo-500/20 flex items-center">
                    <i class="fa-solid fa-plus mr-2"></i> Buat Laporan Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-lg"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Informational Panel -->
            <div class="bg-indigo-950/30 border border-indigo-500/30 rounded-2xl p-6 shadow-xl backdrop-blur-xl">
                <div class="flex">
                    <div class="flex-shrink-0 bg-indigo-500/10 p-3 rounded-xl border border-indigo-500/20">
                        <i class="fa-solid fa-wand-magic-sparkles text-indigo-400 text-xl"></i>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-md font-semibold text-indigo-200">Custom Ad-Hoc Report Engine</h3>
                        <p class="text-sm text-slate-400 mt-1">Gunakan Custom Report Builder untuk menghasilkan laporan kustom secara instan. Pilih dari berbagai klaster data sekolah seperti profil siswa, nilai akademik, riwayat absensi, hingga rekam jejak tunggakan SPP. Anda dapat menyusun kolom, memfilter data, memvisualisasikan laporan menjadi grafik interaktif, serta mengekspornya ke berkas Excel atau PDF cetak Landscape.</p>
                    </div>
                </div>
            </div>

            <!-- Templates List Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($reports as $report)
                    <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-indigo-500/30 flex flex-col group">
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="inline-flex items-center px-2.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        @if(($report->config['source'] ?? '') === 'students')
                                            <i class="fa-solid fa-user-graduate mr-1"></i> Data Siswa
                                        @elseif(($report->config['source'] ?? '') === 'billings')
                                            <i class="fa-solid fa-wallet mr-1"></i> Keuangan SPP
                                        @elseif(($report->config['source'] ?? '') === 'attendances')
                                            <i class="fa-solid fa-calendar-check mr-1"></i> Kehadiran
                                        @else
                                            <i class="fa-solid fa-graduation-cap mr-1"></i> Akademik / Nilai
                                        @endif
                                    </span>
                                    
                                    @if($report->is_shared)
                                        <span class="text-xs text-indigo-400 font-semibold flex items-center bg-indigo-500/5 border border-indigo-500/10 px-2 py-0.5 rounded-full">
                                            <i class="fa-solid fa-globe mr-1 text-[10px]"></i> Shared
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-semibold flex items-center bg-slate-500/5 border border-slate-500/10 px-2 py-0.5 rounded-full">
                                            <i class="fa-solid fa-lock mr-1 text-[10px]"></i> Privat
                                        </span>
                                    @endif
                                </div>
                                
                                <h3 class="text-lg font-bold text-slate-200 mt-4 group-hover:text-indigo-400 transition-colors">
                                    {{ $report->name }}
                                </h3>
                                <p class="text-sm text-slate-400 mt-2 line-clamp-3">
                                    {{ $report->description ?: 'Tidak ada deskripsi disediakan.' }}
                                </p>
                            </div>

                            <div class="mt-6 border-t border-slate-700/50 pt-4 text-xs text-slate-500 flex items-center justify-between">
                                <span class="flex items-center">
                                    <i class="fa-solid fa-user mr-1"></i> {{ $report->creator->name ?? 'N/A' }}
                                </span>
                                <span>
                                    {{ $report->created_at->format('d M Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Action Bar -->
                        <div class="px-6 py-4 bg-slate-900/40 border-t border-slate-700/50 flex justify-between items-center space-x-2">
                            <a href="{{ route('admin.report-builder.show', $report->uuid) }}" id="btn-run-{{ $report->id }}" class="flex-1 text-center py-2 bg-indigo-600/10 hover:bg-indigo-600 text-indigo-300 hover:text-white text-xs font-semibold rounded-lg transition-all duration-200 flex items-center justify-center">
                                <i class="fa-solid fa-circle-play mr-1.5"></i> Jalankan
                            </a>
                            
                            <div class="flex space-x-1">
                                <a href="{{ route('admin.report-builder.export.excel', $report->uuid) }}" class="p-2 bg-emerald-500/10 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-lg transition-all duration-200" title="Unduh Excel">
                                    <i class="fa-solid fa-file-excel"></i>
                                </a>
                                <a href="{{ route('admin.report-builder.export.pdf', $report->uuid) }}" class="p-2 bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg transition-all duration-200" title="Unduh PDF">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </a>
                                
                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'superadmin' || $report->created_by === auth()->id())
                                    <form action="{{ route('admin.report-builder.destroy', $report->uuid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus template laporan ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" id="btn-delete-{{ $report->id }}" class="p-2 bg-rose-500/10 hover:bg-rose-600 text-rose-400 hover:text-white rounded-lg transition-all duration-200" title="Hapus Template">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-3 bg-slate-800/40 border border-slate-700/50 rounded-2xl p-12 text-center text-slate-400">
                        <i class="fa-solid fa-chart-column text-5xl text-slate-600 mb-4 block"></i>
                        <h4 class="text-lg font-semibold text-slate-200">Belum Ada Laporan Kustom</h4>
                        <p class="text-sm mt-2 max-w-md mx-auto">Anda belum membuat atau memiliki akses ke laporan kustom manapun. Tekan tombol di pojok kanan atas untuk meluncurkan antarmuka pembuatan laporan!</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
