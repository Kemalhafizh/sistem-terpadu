<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">{{ $exam->title }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $statColor = match($exam->status) { 'draft' => 'bg-slate-500/10 text-slate-500 border-slate-500/20', 'published' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20', 'completed' => 'bg-blue-500/10 text-blue-600 border-blue-500/20', default => 'bg-slate-500/10 text-slate-600 border-slate-500/20' };
                    @endphp
                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statColor }}">{{ strtoupper($exam->status) }}</span>
                    <span class="text-xs text-slate-400">oleh {{ $exam->creator?->name }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($exam->status === 'published')
                    <a href="{{ route('cbt.exams.monitor', $exam) }}" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-xl text-xs font-extrabold shadow-md transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        Monitor Live
                    </a>
                @endif
                <a href="{{ route('cbt.exams.results', $exam) }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all flex items-center gap-1.5">Lihat Hasil</a>
                @if(auth()->user()->role !== 'siswa')
                    <a href="{{ route('cbt.exams.analysis', $exam) }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all flex items-center gap-1.5">Analisis Butir Soal</a>
                @endif
                @can('update', $exam)
                    <a href="{{ route('cbt.exams.edit', $exam) }}" class="px-4 py-2.5 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">Edit</a>
                @endcan
                <a href="{{ route('cbt.exams.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6 max-w-5xl mx-auto">

        {{-- Info Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Mata Pelajaran', 'value' => $exam->subject?->name ?? '-', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'blue'],
                ['label' => 'Kelas', 'value' => $exam->classroom?->name ?? 'Semua Kelas', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'emerald'],
                ['label' => 'Durasi', 'value' => $exam->duration_minutes . ' menit', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber'],
                ['label' => 'Total Soal', 'value' => $exam->total_questions . ' soal', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'color' => 'indigo'],
            ] as $info)
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $info['label'] }}</p>
                    <p class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $info['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Schedule & Security --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-black text-slate-800 dark:text-white">Jadwal Ujian</h4>
                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1.5">
                    <p><span class="font-bold text-slate-400">Mulai:</span> {{ $exam->start_time->format('d M Y, H:i') }} WIB</p>
                    <p><span class="font-bold text-slate-400">Selesai:</span> {{ $exam->end_time->format('d M Y, H:i') }} WIB</p>
                    <p><span class="font-bold text-slate-400">Tahun Ajaran:</span> {{ $exam->academicYear?->year ?? '-' }}</p>
                </div>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-black text-slate-800 dark:text-white">Pengaturan Keamanan</h4>
                <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1.5">
                    <p>Acak Soal: <span class="font-bold {{ $exam->shuffle_questions ? 'text-emerald-600' : 'text-red-500' }}">{{ $exam->shuffle_questions ? 'Aktif' : 'Nonaktif' }}</span></p>
                    <p>Acak Opsi: <span class="font-bold {{ $exam->shuffle_options ? 'text-emerald-600' : 'text-red-500' }}">{{ $exam->shuffle_options ? 'Aktif' : 'Nonaktif' }}</span></p>
                    <p>Mode Anti-Curang: <span class="font-bold {{ $exam->is_secure_mode ? 'text-emerald-600' : 'text-red-500' }}">{{ $exam->is_secure_mode ? 'Aktif' : 'Nonaktif' }}</span></p>
                    <p>Maks. Pelanggaran: <span class="font-bold text-red-600">{{ $exam->max_violations }}x</span></p>
                    <p>Nilai KKM: <span class="font-bold">{{ $exam->passing_score }}</span></p>
                </div>
            </div>
        </div>

        {{-- Questions List --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-4">
            <h4 class="text-sm font-black text-slate-800 dark:text-white">Daftar Soal ({{ $exam->questions->count() }})</h4>
            <div class="space-y-3">
                @foreach($exam->questions->sortBy('pivot.order') as $i => $q)
                    <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 dark:border-slate-700 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-all" x-data="{ showAnswer: false }">
                        <span class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center text-xs font-black shrink-0">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-800 dark:text-white">{{ $q->question_text }}</p>
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                @php $dColor = match($q->difficulty) { 'easy' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20', 'medium' => 'bg-amber-500/10 text-amber-600 border-amber-500/20', 'hard' => 'bg-red-500/10 text-red-600 border-red-500/20', default => '' }; @endphp
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase border {{ $dColor }}">{{ $q->difficulty }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-slate-500/10 text-slate-500 border border-slate-500/20">{{ $q->type === 'multiple_choice' ? 'PG' : ($q->type === 'true_false' ? 'B/S' : 'Esai') }}</span>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black text-slate-400">Bobot: {{ $q->pivot->weight }}</span>
                            </div>
                            <div x-show="showAnswer" x-transition class="mt-2 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50">
                                <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-400">Jawaban: {{ $q->correct_answer }}</p>
                                @if($q->explanation)<p class="text-[10px] text-slate-500 mt-1">{{ $q->explanation }}</p>@endif
                            </div>
                        </div>
                        <button type="button" @click="showAnswer = !showAnswer" class="text-[10px] font-bold text-blue-600 hover:underline shrink-0" x-text="showAnswer ? 'Sembunyikan' : 'Lihat Kunci'"></button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
