<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Edit Soal</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui data soal #{{ $questionBank->id }}.</p>
            </div>
            <a href="{{ route('cbt.question-banks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ questionType: '{{ old('type', $questionBank->type) }}' }">
        <form action="{{ route('cbt.question-banks.update', $questionBank) }}" method="POST" enctype="multipart/form-data" class="space-y-6 max-w-4xl mx-auto">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800/50 rounded-2xl p-4">
                    <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight">Informasi Soal</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mata Pelajaran *</label>
                        <select name="subject_id" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id', $questionBank->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kelas *</label>
                        <select name="grade_level" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            @foreach(['X','XI','XII'] as $g)
                                <option value="{{ $g }}" {{ old('grade_level', $questionBank->grade_level) == $g ? 'selected' : '' }}>Kelas {{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipe Soal *</label>
                        <select name="type" required x-model="questionType" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="true_false">Benar / Salah</option>
                            <option value="essay">Esai</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tingkat Kesulitan *</label>
                        <select name="difficulty" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            @foreach(['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'] as $k => $v)
                                <option value="{{ $k }}" {{ old('difficulty', $questionBank->difficulty) == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kode KD (Opsional)</label>
                        <input type="text" name="competency_code" value="{{ old('competency_code', $questionBank->competency_code) }}" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight">Konten Soal</h3>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teks Pertanyaan *</label>
                    <textarea name="question_text" rows="4" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('question_text', $questionBank->question_text) }}</textarea>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gambar Soal (Opsional)</label>
                    @if($questionBank->question_image)
                        <div class="mb-2"><img src="{{ Storage::url($questionBank->question_image) }}" alt="Gambar soal" class="w-32 h-32 object-cover rounded-xl border border-slate-200 dark:border-slate-700"></div>
                    @endif
                    <input type="file" name="question_image" accept="image/*" class="w-full text-xs font-semibold file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950 dark:file:text-blue-400 text-slate-500">
                </div>

                <div x-show="questionType === 'multiple_choice'" x-transition class="space-y-3">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Opsi Jawaban *</label>
                    @php $opts = old('options', $questionBank->options ?? []); @endphp
                    @foreach(['A','B','C','D','E'] as $opt)
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-black text-slate-600 dark:text-slate-300 shrink-0">{{ $opt }}</span>
                            <input type="text" name="options[{{ $opt }}]" value="{{ $opts[$opt] ?? '' }}" placeholder="Opsi {{ $opt }}" class="flex-1 text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        </div>
                    @endforeach
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jawaban Benar *</label>
                    <template x-if="questionType === 'multiple_choice'">
                        <select name="correct_answer" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            @foreach(['A','B','C','D','E'] as $opt)
                                <option value="{{ $opt }}" {{ old('correct_answer', $questionBank->correct_answer) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </template>
                    <template x-if="questionType === 'true_false'">
                        <select name="correct_answer" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="true" {{ old('correct_answer', $questionBank->correct_answer) == 'true' ? 'selected' : '' }}>Benar</option>
                            <option value="false" {{ old('correct_answer', $questionBank->correct_answer) == 'false' ? 'selected' : '' }}>Salah</option>
                        </select>
                    </template>
                    <template x-if="questionType === 'essay'">
                        <textarea name="correct_answer" rows="2" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('correct_answer', $questionBank->correct_answer) }}</textarea>
                    </template>
                </div>

                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pembahasan (Opsional)</label>
                    <textarea name="explanation" rows="3" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">{{ old('explanation', $questionBank->explanation) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-extrabold shadow-lg shadow-blue-500/20 transition-all flex items-center gap-2 border border-blue-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Perbarui Soal
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
