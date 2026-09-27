<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Edit Ujian</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ubah informasi, jadwal dan pengaturan keamanan. Soal dikelola dari halaman detail ujian.</p>
            </div>
            <a href="{{ route('cbt.exams.show', $exam) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        shuffleQuestions: @js((bool) old('shuffle_questions', $exam->shuffle_questions)),
        shuffleOptions: @js((bool) old('shuffle_options', $exam->shuffle_options)),
        secureMode: @js((bool) old('is_secure_mode', $exam->is_secure_mode)),
    }">
        <form action="{{ route('cbt.exams.update', $exam) }}" method="POST" class="space-y-6 max-w-5xl mx-auto">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800/50 rounded-2xl p-4">
                    <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-400 space-y-1">
                        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            {{-- Informasi Ujian --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    Informasi Ujian
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Judul Ujian *</label>
                        <input type="text" name="title" value="{{ old('title', $exam->title) }}" required placeholder="Contoh: UTS Matematika Kelas X" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jenis Ujian *</label>
                        <select name="type" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="ulangan_harian" {{ old('type', $exam->type) == 'ulangan_harian' ? 'selected' : '' }}>Ulangan Harian</option>
                            <option value="uts" {{ old('type', $exam->type) == 'uts' ? 'selected' : '' }}>UTS</option>
                            <option value="uas" {{ old('type', $exam->type) == 'uas' ? 'selected' : '' }}>UAS</option>
                            <option value="try_out" {{ old('type', $exam->type) == 'try_out' ? 'selected' : '' }}>Try Out</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mata Pelajaran *</label>
                        <select name="subject_id" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects as $s)<option value="{{ $s->id }}" {{ old('subject_id', $exam->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kelas (Kosongkan = Semua)</label>
                        <select name="classroom_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $c)<option value="{{ $c->id }}" {{ old('classroom_id', $exam->classroom_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tahun Ajaran *</label>
                        <select name="academic_year_id" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id', $exam->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->year }} - Semester {{ $ay->semester }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Jadwal & Durasi --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    Jadwal & Durasi
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu Mulai *</label>
                        <input type="datetime-local" name="start_time" value="{{ old('start_time', $exam->start_time?->format('Y-m-d\TH:i')) }}" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Waktu Selesai *</label>
                        <input type="datetime-local" name="end_time" value="{{ old('end_time', $exam->end_time?->format('Y-m-d\TH:i')) }}" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Durasi (Menit) *</label>
                        <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="5" max="300" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Pengaturan Keamanan --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm space-y-5">
                <h3 class="text-sm font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg></div>
                    Pengaturan Keamanan Anti-Curang
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all cursor-pointer">
                        <input type="hidden" name="shuffle_questions" value="0">
                        <input type="checkbox" name="shuffle_questions" value="1" x-model="shuffleQuestions" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-white">Acak Urutan Soal</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Setiap siswa mendapatkan urutan soal yang berbeda</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all cursor-pointer">
                        <input type="hidden" name="shuffle_options" value="0">
                        <input type="checkbox" name="shuffle_options" value="1" x-model="shuffleOptions" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-white">Acak Opsi Jawaban</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pilihan A/B/C/D/E diacak per siswa</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-xl border border-red-200 dark:border-red-800/50 bg-red-50/50 dark:bg-red-950/20 hover:bg-red-100/50 dark:hover:bg-red-950/30 transition-all cursor-pointer md:col-span-2">
                        <input type="hidden" name="is_secure_mode" value="0">
                        <input type="checkbox" name="is_secure_mode" value="1" x-model="secureMode" class="w-4 h-4 rounded border-gray-300 dark:border-slate-600 text-red-600 focus:ring-red-500">
                        <div>
                            <p class="text-xs font-bold text-slate-800 dark:text-white"> Mode Anti-Curang Penuh</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Fullscreen lock, deteksi tab switch, getaran peringatan, blokir copy-paste, dan pemantauan real-time</p>
                        </div>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Maks. Pelanggaran Sebelum Blokir</label>
                        <input type="number" name="max_violations" value="{{ old('max_violations', $exam->max_violations) }}" min="1" max="10" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <p class="text-[10px] text-slate-400">Ujian akan dikunci otomatis setelah pelanggaran ini terlampaui</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nilai KKM / Passing Score</label>
                        <input type="number" name="passing_score" value="{{ old('passing_score', $exam->passing_score) }}" min="0" max="100" step="0.01" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-sm font-extrabold shadow-lg shadow-emerald-500/20 transition-all flex items-center gap-2 border border-emerald-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
