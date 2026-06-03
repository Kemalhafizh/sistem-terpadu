<div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 flex flex-col justify-between group">
    <div>
        <div class="flex items-center justify-between mb-4">
            <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-full border {{ $badgeColor }}">
                {{ $survey->status }}
            </span>
            <span class="px-2.5 py-1 text-[9px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 rounded-full">
                {{ $survey->is_anonymous ? 'Anonim' : 'Identitas Terbuka' }}
            </span>
        </div>

        <h3 class="text-lg font-black tracking-tight text-slate-800 dark:text-white mb-2 leading-tight group-hover:text-emerald-500 transition-colors">
            {{ $survey->title }}
        </h3>
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 line-clamp-2 mb-4">
            {{ $survey->description ?? 'Tidak ada deskripsi.' }}
        </p>

        <!-- Meta list -->
        <div class="space-y-2 border-t border-b border-slate-100 dark:border-slate-700/50 py-3 mb-6">
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span class="font-bold">Target Stakeholder:</span>
                <span class="font-black text-slate-700 dark:text-slate-300">
                    {{ $survey->target_role ? ucfirst($survey->target_role) : 'Semua' }}
                    @if($survey->classroom)
                        ({{ $survey->classroom->name }})
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                <span class="font-bold">Total Respons:</span>
                <span class="font-black text-emerald-500">{{ $survey->responses()->count() }} Jawaban</span>
            </div>
            @if($survey->start_date)
                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-bold">Waktu Mulai:</span>
                    <span class="font-semibold">{{ $survey->start_date->format('d M Y H:i') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col space-y-2">
        @if($survey->status === 'draft')
            <div class="flex space-x-2">
                <form action="{{ route('surveys.activate', $survey->uuid) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all border border-emerald-400/20">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Aktifkan
                    </button>
                </form>
                <a href="{{ route('surveys.show', $survey->uuid) }}" 
                   class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-white rounded-xl text-xs font-bold transition-all">
                    Pratinjau
                </a>
            </div>
            
            <form action="{{ route('surveys.destroy', $survey->uuid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus draf survei ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-rose-50/50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-bold transition-all border border-rose-100 dark:border-rose-950/30">
                    Hapus Draf
                </button>
            </form>
        @endif

        @if($survey->status === 'active' || $survey->status === 'closed')
            <div class="flex space-x-2">
                <a href="{{ route('surveys.results', $survey->uuid) }}" 
                   class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl text-xs font-bold shadow-md transition-all border border-emerald-400/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Analitik
                </a>
                
                @if($survey->status === 'active')
                    <form action="{{ route('surveys.close', $survey->uuid) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold shadow-md transition-all border border-rose-400/20">
                            Tutup
                        </button>
                    </form>
                @endif
            </div>

            <!-- Export Actions -->
            <div class="flex space-x-2">
                <a href="{{ route('surveys.export.pdf', $survey->uuid) }}" 
                   class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700/60 hover:bg-slate-100 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-bold border border-slate-200/50 dark:border-slate-700 transition-all">
                    PDF Report
                </a>
                <a href="{{ route('surveys.export.excel', $survey->uuid) }}" 
                   class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700/60 hover:bg-slate-100 text-slate-600 dark:text-slate-300 rounded-xl text-[10px] font-bold border border-slate-200/50 dark:border-slate-700 transition-all">
                    Excel Data
                </a>
            </div>

            @if($survey->status === 'closed')
                <form action="{{ route('surveys.destroy', $survey->uuid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus survei ini secara permanen?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl text-[11px] font-bold transition-all">
                        Hapus Permanen
                    </button>
                </form>
            @endif
        @endif
    </div>
</div>
