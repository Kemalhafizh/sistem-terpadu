<x-app-layout>
    <x-slot name="title">Pratinjau Kuesioner: {{ $survey->title }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Survei Digital', 'url' => route('surveys.index')],
            ['label' => 'Pratinjau', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <a href="{{ route('surveys.index') }}" 
                   class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <span class="px-3 py-1 text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-750 text-slate-600 dark:text-slate-300 border border-slate-200/50 rounded-full inline-block mb-1">
                        Draf Survei & Kuesioner
                    </span>
                    <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white leading-tight">
                        Pratinjau Kuesioner
                    </h1>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                @if($survey->status === 'draft')
                    <form action="{{ route('surveys.activate', $survey->uuid) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-500/10 border border-emerald-400/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Aktifkan & Publikasikan
                        </button>
                    </form>
                @elseif($survey->status === 'active')
                    <form action="{{ route('surveys.close', $survey->uuid) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-rose-500/10 border border-rose-400/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                            Tutup Kuesioner
                        </button>
                    </form>
                @endif
                
                <form action="{{ route('surveys.destroy', $survey->uuid) }}" method="POST" class="inline" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus/mengarsipkan survei ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/20 dark:text-slate-300 dark:hover:text-rose-400 rounded-2xl text-xs font-black uppercase tracking-wider transition-all border border-slate-200/50 dark:border-slate-700/50">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus Draf
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-8">
        
        <!-- Summary Panel / Config display -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Detail Pengaturan Survei</h2>
                </div>
                <span class="px-3 py-1 text-xs font-extrabold uppercase rounded-full {{ $survey->status === 'draft' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' }}">
                    Status: {{ $survey->status }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Info Left -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Judul Kuesioner</h4>
                        <p class="text-base font-extrabold text-slate-800 dark:text-white mt-1">{{ $survey->title }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Deskripsi & Petunjuk</h4>
                        <p class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                            {{ $survey->description ?? 'Tidak ada deskripsi.' }}
                        </p>
                    </div>
                </div>

                <!-- Info Right -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Sifat Identitas</h4>
                        <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">
                            {{ $survey->is_anonymous ? 'Anonim (DIPROTEKSI)' : 'Terbuka (Identitas Terlihat)' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Target Peran</h4>
                        <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1 uppercase">
                            {{ $survey->target_role ?? 'Semua Peran' }}
                        </p>
                    </div>
                    @if($survey->classroom)
                        <div>
                            <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Target Kelas</h4>
                            <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">{{ $survey->classroom->name }}</p>
                        </div>
                    @endif
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Kuota Maksimum</h4>
                        <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">
                            {{ $survey->max_responses ?? 'Tanpa Batas' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Aktif Mulai</h4>
                        <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">
                            {{ $survey->start_date ? $survey->start_date->format('d M Y H:i') : 'Sekarang / Segera' }}
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Batas Akhir (Deadline)</h4>
                        <p class="text-xs font-extrabold text-slate-700 dark:text-slate-300 mt-1">
                            {{ $survey->end_date ? $survey->end_date->format('d M Y H:i') : 'Tanpa Batas' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Preview Forms rendering -->
        <div class="space-y-4">
            <h2 class="text-lg font-black tracking-tight text-slate-800 dark:text-white pl-1">
                Struktur Butir Kuesioner (Pratinjau Tampilan)
            </h2>

            @foreach($survey->questions as $index => $question)
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-4">
                    
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                        <span class="text-xs font-black text-slate-500 dark:text-slate-400">
                            Butir {{ $index + 1 }} &bull; Tipe: <span class="uppercase">{{ $question->type }}</span>
                        </span>
                        @if($question->is_required)
                            <span class="text-[10px] font-black uppercase bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400 px-2 py-0.5 rounded border border-rose-200/30">
                                Wajib Dijawab
                            </span>
                        @endif
                    </div>

                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white leading-snug">
                        {{ $question->question_text }}
                    </h3>

                    <!-- Disabled Preview Form Field -->
                    <div class="pt-2 opacity-60">
                        @if($question->type === 'text')
                            <input type="text" disabled placeholder="Teks jawaban singkat..."
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold">
                        
                        @elseif($question->type === 'textarea')
                            <textarea disabled rows="3" placeholder="Esai tanggapan panjang..."
                                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold"></textarea>
                        
                        @elseif(in_array($question->type, ['radio', 'select', 'checkbox']))
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($question->options as $option)
                                    <div class="flex items-center p-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                        <div class="w-4.5 h-4.5 rounded-full border-2 border-slate-350 dark:border-slate-650 mr-2 flex-shrink-0"></div>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $option }}</span>
                                    </div>
                                @endforeach
                            </div>

                        @elseif($question->type === 'scale')
                            <div class="space-y-3 py-1">
                                @if($question->scale_min_label || $question->scale_max_label)
                                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 px-1">
                                        <span>{{ $question->scale_min_label ?? $question->scale_min }}</span>
                                        <span>{{ $question->scale_max_label ?? $question->scale_max }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between gap-2">
                                    @for($rating = $question->scale_min; $rating <= $question->scale_max; $rating++)
                                        <div class="flex-1 flex flex-col items-center justify-center py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                            <div class="w-4 h-4 rounded-full border border-slate-350 dark:border-slate-650 mb-1"></div>
                                            <span class="text-xs font-extrabold text-slate-500">{{ $rating }}</span>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                        @elseif($question->type === 'date')
                            <input type="date" disabled
                                   class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs font-semibold">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
