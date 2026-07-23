<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Tambah Soal Baru</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tambahkan soal baru ke Bank Soal untuk digunakan dalam ujian CBT.</p>
            </div>
            <a href="{{ route('cbt.question-banks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        questionType: '{{ old('type', 'multiple_choice') }}',
    }">
        <form action="{{ route('cbt.question-banks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-4xl mx-auto">
            @csrf

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800/50 rounded-2xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="text-sm font-bold text-red-700 dark:text-red-400">Terdapat kesalahan:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Card: Informasi Soal --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                    Informasi Soal
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Mata Pelajaran --}}
                    <div class="space-y-1.5">
                        <label for="subject_id" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mata Pelajaran *</label>
                        <select name="subject_id" id="subject_id" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tingkat Kelas --}}
                    <div class="space-y-1.5">
                        <label for="grade_level" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kelas *</label>
                        <select name="grade_level" id="grade_level" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="X" {{ old('grade_level') == 'X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="XI" {{ old('grade_level') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="XII" {{ old('grade_level') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                    </div>

                    {{-- Tipe Soal --}}
                    <div class="space-y-1.5">
                        <label for="type" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipe Soal *</label>
                        <select name="type" id="type" required x-model="questionType" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="true_false">Benar / Salah</option>
                            <option value="essay">Esai</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Tingkat Kesulitan --}}
                    <div class="space-y-1.5">
                        <label for="difficulty" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kesulitan *</label>
                        <select name="difficulty" id="difficulty" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="easy" {{ old('difficulty') == 'easy' ? 'selected' : '' }}>Mudah</option>
                            <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Sedang</option>
                            <option value="hard" {{ old('difficulty') == 'hard' ? 'selected' : '' }}>Sulit</option>
                        </select>
                    </div>

                    {{-- Kode KD --}}
                    <div class="space-y-1.5">
                        <label for="competency_code" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode KD (Opsional)</label>
                        <input type="text" name="competency_code" id="competency_code" value="{{ old('competency_code') }}" placeholder="Contoh: KD 3.1" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Card: Konten Soal --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></div>
                    Konten Soal
                </h3>

                {{-- Teks Soal --}}
                <div class="space-y-1.5">
                    <label for="question_text" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teks Pertanyaan *</label>
                    <textarea name="question_text" id="question_text" rows="4" required placeholder="Tuliskan soal di sini (dukung notasi LaTeX $...$ atau $$...$)..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('question_text') }}</textarea>
                    <x-latex-toolbar target-id="question_text" />
                </div>

                {{-- Gambar Soal --}}
                <div class="space-y-1.5">
                    <label for="question_image" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gambar Soal (Opsional)</label>
                    <input type="file" name="question_image" id="question_image" accept="image/*" class="w-full text-xs font-semibold file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-400 text-slate-500 dark:text-slate-400">
                </div>

                {{-- Opsi Pilihan Ganda --}}
                <div x-show="questionType === 'multiple_choice'" x-transition class="space-y-3">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Opsi Jawaban *</label>
                    @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-black text-slate-600 dark:text-slate-300 shrink-0">{{ $opt }}</span>
                            <input type="text" name="options[{{ $opt }}]" value="{{ old('options.'.$opt) }}" placeholder="Opsi {{ $opt }}" class="flex-1 text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        </div>
                    @endforeach
                </div>

                {{-- Jawaban Benar --}}
                <div class="space-y-1.5">
                    <label for="correct_answer" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jawaban Benar *</label>

                    <template x-if="questionType === 'multiple_choice'">
                        <select name="correct_answer" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih --</option>
                            @foreach(['A', 'B', 'C', 'D', 'E'] as $opt)
                                <option value="{{ $opt }}" {{ old('correct_answer') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </template>

                    <template x-if="questionType === 'true_false'">
                        <select name="correct_answer" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih --</option>
                            <option value="true" {{ old('correct_answer') == 'true' ? 'selected' : '' }}>Benar</option>
                            <option value="false" {{ old('correct_answer') == 'false' ? 'selected' : '' }}>Salah</option>
                        </select>
                    </template>

                    <template x-if="questionType === 'essay'">
                        <textarea name="correct_answer" rows="2" placeholder="Kunci jawaban esai..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('correct_answer') }}</textarea>
                    </template>
                </div>

                {{-- Penjelasan --}}
                <div class="space-y-1.5">
                    <label for="explanation" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pembahasan (Opsional)</label>
                    <textarea name="explanation" id="explanation" rows="3" placeholder="Tulis pembahasan jawaban di sini..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('explanation') }}</textarea>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-sm font-extrabold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all flex items-center gap-2 border border-emerald-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
