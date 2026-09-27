<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight">
                    {{ __('Bank Soal') }}
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Kelola koleksi soal ujian CBT untuk semua mata pelajaran dan tingkat kelas
                </p>
            </div>
            <a href="{{ route('cbt.question-banks.create') }}"
               class="inline-flex items-center space-x-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/20 transition-all duration-200 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Soal</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Success Alert --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Filter Bar --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5">
            <form action="{{ route('cbt.question-banks.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                {{-- Subject Filter --}}
                <div class="flex-1 w-full space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Mata Pelajaran</label>
                    <select name="subject_id"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Difficulty Filter --}}
                <div class="flex-1 w-full space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Tingkat Kesulitan</label>
                    <select name="difficulty"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                        <option value="">Semua Tingkat</option>
                        <option value="easy" {{ request('difficulty') === 'easy' ? 'selected' : '' }}>Mudah</option>
                        <option value="medium" {{ request('difficulty') === 'medium' ? 'selected' : '' }}>Sedang</option>
                        <option value="hard" {{ request('difficulty') === 'hard' ? 'selected' : '' }}>Sulit</option>
                    </select>
                </div>

                {{-- Grade Level Filter --}}
                <div class="flex-1 w-full space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Tingkat Kelas</label>
                    <select name="grade_level"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                        <option value="">Semua Kelas</option>
                        <option value="X" {{ request('grade_level') === 'X' ? 'selected' : '' }}>Kelas X</option>
                        <option value="XI" {{ request('grade_level') === 'XI' ? 'selected' : '' }}>Kelas XI</option>
                        <option value="XII" {{ request('grade_level') === 'XII' ? 'selected' : '' }}>Kelas XII</option>
                    </select>
                </div>

                {{-- Filter Actions --}}
                <div class="flex items-center space-x-2">
                    <button type="submit"
                            class="px-5 py-2.5 bg-gray-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-800 dark:hover:bg-white transition-all shadow-md active:scale-95">
                        <svg class="w-4 h-4 inline-block mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['subject_id', 'difficulty', 'grade_level']))
                        <a href="{{ route('cbt.question-banks.index') }}"
                           class="px-4 py-2.5 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 dark:hover:bg-slate-700 transition-all"
                           title="Reset filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Question Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($questions as $q)
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between hover:shadow-xl hover:shadow-gray-200/50 dark:hover:shadow-slate-900/50 hover:-translate-y-1 transition-all duration-300 group"
                     x-data="{ showDelete: false }">
                    <div class="space-y-4">
                        {{-- Top Badges Row --}}
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Subject Badge --}}
                            <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border-indigo-200 dark:border-indigo-900/50">
                                {{ $q->subject->name ?? '-' }}
                            </span>

                            {{-- Difficulty Badge --}}
                            @if($q->difficulty === 'easy')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50">
                                    Mudah
                                </span>
                            @elseif($q->difficulty === 'medium')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-900/50">
                                    Sedang
                                </span>
                            @elseif($q->difficulty === 'hard')
                                <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900/50">
                                    Sulit
                                </span>
                            @endif

                            {{-- Grade Level Badge --}}
                            <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border bg-gray-50 dark:bg-slate-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-slate-700">
                                Kelas {{ $q->grade_level }}
                            </span>
                        </div>

                        {{-- Question Type Badge --}}
                        <div>
                            @if($q->type === 'multiple_choice')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span>Pilihan Ganda</span>
                                </span>
                            @elseif($q->type === 'true_false')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-900/50">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Benar-Salah</span>
                                </span>
                            @elseif($q->type === 'essay')
                                <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 border border-teal-200 dark:border-teal-900/50">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Esai</span>
                                </span>
                            @endif
                        </div>

                        {{-- Question Text --}}
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed font-medium">
                            {{ Str::limit(strip_tags($q->question_text), 120) }}
                        </p>
                    </div>

                    {{-- Footer: Creator & Actions --}}
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-slate-800/80">
                        <div class="flex items-center justify-between">
                            {{-- Creator Info --}}
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center">
                                    <span class="text-[8px] font-black text-white uppercase">{{ substr($q->creator->name ?? '?', 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-700 dark:text-gray-300 leading-none">{{ $q->creator->name ?? '-' }}</p>
                                    <p class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $q->created_at->format('d M Y') }}</p>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center space-x-1.5">
                                <a href="{{ route('cbt.question-banks.show', $q) }}"
                                   class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-emerald-50 dark:hover:bg-emerald-950 hover:text-emerald-600 transition-all"
                                   title="Lihat Soal">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('cbt.question-banks.edit', $q) }}"
                                   class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-200 dark:hover:border-blue-900 transition-all duration-200"
                                   title="Edit Soal">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button @click="showDelete = true"
                                        class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:text-rose-600 dark:hover:text-rose-400 hover:border-rose-200 dark:hover:border-rose-900 transition-all duration-200"
                                        title="Hapus Soal">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Delete Confirmation --}}
                        <div x-show="showDelete" x-transition x-cloak
                             class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 rounded-xl">
                            <p class="text-[10px] font-bold text-rose-700 dark:text-rose-400 mb-2">Yakin ingin menghapus soal ini?</p>
                            <div class="flex items-center space-x-2">
                                <form action="{{ route('cbt.question-banks.destroy', $q) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                                        Hapus
                                    </button>
                                </form>
                                <button @click="showDelete = false"
                                        class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-gray-300 rounded-lg text-[10px] font-bold hover:bg-gray-50 dark:hover:bg-slate-700 transition-all">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State --}}
                <div class="md:col-span-2 lg:col-span-3 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5">
                            <svg class="w-10 h-10 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Belum Ada Soal</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mb-6 leading-relaxed">
                            Bank soal masih kosong. Mulai tambahkan soal untuk mata pelajaran dan tingkat kelas yang dibutuhkan.
                        </p>
                        <a href="{{ route('cbt.question-banks.create') }}"
                           class="inline-flex items-center space-x-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-600/20 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Soal Pertama</span>
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($questions->hasPages())
            <div class="mt-6">
                {{ $questions->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
