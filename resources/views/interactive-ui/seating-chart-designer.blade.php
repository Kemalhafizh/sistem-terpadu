<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Desainer Denah Tempat Duduk Kelas') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Susun formasi meja dan tetapkan tempat duduk siswa interaktif dengan visualisasi perilaku.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Student List Sidebar -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center">
                    <svg class="w-4 h-4 mr-2 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Siswa Belum Duduk
                </h3>
                <div class="space-y-2 max-h-[500px] overflow-y-auto pr-1">
                    @foreach($students as $st)
                        <div draggable="true" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 cursor-move hover:border-cyan-500 transition-all flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-xs text-slate-900 dark:text-white">{{ $st->nama_lengkap ?? $st->name }}</h4>
                                <span class="text-[10px] text-slate-500">NIS: {{ $st->nis ?? '-' }}</span>
                            </div>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Classroom Layout Canvas -->
            <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Tata Letak Meja & Kursi</h3>
                        <p class="text-xs text-slate-500">Papan Tulis Guru Berada di Sisi Atas</p>
                    </div>
                    <div class="w-48 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl text-center border border-slate-200 dark:border-slate-700">
                        [ Papan Tulis / Meja Guru ]
                    </div>
                </div>

                <!-- Grid Visual 5x6 -->
                <div class="grid grid-cols-6 gap-3 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-3xl border border-slate-100 dark:border-slate-700/60">
                    @for($r = 0; $r < 5; $r++)
                        @for($c = 0; $c < 6; $c++)
                            <div class="aspect-video rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 p-2 flex flex-col items-center justify-center text-center bg-white dark:bg-slate-900 hover:border-emerald-500 transition-all">
                                <span class="text-[10px] font-bold text-slate-400">Meja {{ $r+1 }}-{{ $c+1 }}</span>
                                <span class="text-[9px] text-slate-500 mt-1 truncate max-w-full">Tarik Siswa</span>
                            </div>
                        @endfor
                    @endfor
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
