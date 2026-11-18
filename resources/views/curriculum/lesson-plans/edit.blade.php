<x-app-layout>
    <x-slot name="title">Edit RPP</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">
                     Edit Rencana Pelaksanaan Pembelajaran (RPP)
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Modifikasi rencana pembelajaran mingguan Anda sebelum diajukan kembali.
                </p>
            </div>
            <a href="{{ route('curriculum.lesson-plans.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200/60 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-900/40">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span></span> Formulir Edit RPP
                    </h3>
                </div>

                <form action="{{ route('curriculum.lesson-plans.update', $lessonPlan->uuid) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    @if($lessonPlan->status === 'revision_needed' && $lessonPlan->reviewer_notes)
                        <div class="p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 rounded-2xl border border-red-200 dark:border-red-900/50 text-sm">
                            <h4 class="font-bold flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Catatan Revisi dari Waka Kurikulum:
                            </h4>
                            <p class="font-medium pl-7">{{ $lessonPlan->reviewer_notes }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Mata Pelajaran --}}
                        <div>
                            <label for="subject_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select name="subject_id" id="subject_id" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                                <option value="">— Pilih Mata Pelajaran —</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $lessonPlan->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Rombel / Kelas --}}
                        <div>
                            <label for="classroom_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Rombongan Belajar (Kelas) <span class="text-red-500">*</span></label>
                            <select name="classroom_id" id="classroom_id" required
                                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                                <option value="">— Pilih Kelas —</option>
                                @foreach($classrooms as $classroom)
                                    <option value="{{ $classroom->id }}" {{ old('classroom_id', $lessonPlan->classroom_id) == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                            @error('classroom_id')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Topik Utama --}}
                        <div class="md:col-span-2">
                            <label for="topic" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Topik Pembelajaran <span class="text-red-500">*</span></label>
                            <input type="text" name="topic" id="topic" value="{{ old('topic', $lessonPlan->topic) }}" required placeholder="Contoh: Persamaan Kuadrat / Trigonometri Dasar"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                            @error('topic')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Minggu Ke --}}
                        <div>
                            <label for="week_number" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Pertemuan Ke (Minggu Ke-) <span class="text-red-500">*</span></label>
                            <input type="number" name="week_number" id="week_number" value="{{ old('week_number', $lessonPlan->week_number) }}" required min="1" max="52" placeholder="1-52"
                                   class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors">
                            @error('week_number')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="border-gray-200/60 dark:border-gray-700/60">

                    {{-- Standar Kompetensi --}}
                    <div>
                        <label for="competency_standards" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kompetensi Inti & Dasar (KI - KD)</label>
                        <textarea name="competency_standards" id="competency_standards" rows="3" placeholder="Tuliskan Standar Kompetensi / Kompetensi Inti & Kompetensi Dasar..."
                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none">{{ old('competency_standards', $lessonPlan->competency_standards) }}</textarea>
                        @error('competency_standards')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tujuan Pembelajaran --}}
                    <div>
                        <label for="learning_objectives" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Tujuan Pembelajaran <span class="text-red-500">*</span></label>
                        <textarea name="learning_objectives" id="learning_objectives" rows="4" required placeholder="Tuliskan tujuan pembelajaran yang ingin dicapai siswa..."
                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none">{{ old('learning_objectives', $lessonPlan->learning_objectives) }}</textarea>
                        @error('learning_objectives')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kegiatan Pembelajaran --}}
                    <div>
                        <label for="learning_activities" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Langkah & Kegiatan Pembelajaran <span class="text-red-500">*</span></label>
                        <textarea name="learning_activities" id="learning_activities" rows="6" required placeholder="Jelaskan langkah-langkah pembelajaran (Pendahuluan, Inti, Penutup)..."
                                  class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none">{{ old('learning_activities', $lessonPlan->learning_activities) }}</textarea>
                        @error('learning_activities')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Metode Penilaian --}}
                        <div>
                            <label for="assessment_method" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Metode Penilaian & Instrumen</label>
                            <textarea name="assessment_method" id="assessment_method" rows="3" placeholder="Tuliskan metode penilaian (e.g. Tes Tertulis, Observasi, Unjuk Kerja)..."
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none">{{ old('assessment_method', $lessonPlan->assessment_method) }}</textarea>
                            @error('assessment_method')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sumber & Media Belajar --}}
                        <div>
                            <label for="resources" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Sumber & Media Belajar</label>
                            <textarea name="resources" id="resources" rows="3" placeholder="Contoh: Buku Siswa Kemendikbud Kelas X, Proyektor, Google Classroom..."
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none">{{ old('resources', $lessonPlan->resources) }}</textarea>
                            @error('resources')
                                <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="border-gray-200/60 dark:border-gray-700/60">

                    {{-- PDF Upload attachment --}}
                    <div>
                        <label for="file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Perbarui Lampiran RPP (Opsional)</label>
                        @if($lessonPlan->file_path)
                            <div class="mb-3 flex items-center gap-2 p-3 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 rounded-xl border border-emerald-200 dark:border-emerald-900/40 text-xs">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>File aktif: <strong>{{ basename($lessonPlan->file_path) }}</strong></span>
                                <a href="{{ Storage::url($lessonPlan->file_path) }}" target="_blank" class="ml-auto underline hover:text-emerald-900 dark:hover:text-emerald-200 font-bold">Buka File</a>
                            </div>
                        @endif
                        <input type="file" name="file" id="file" accept=".pdf"
                               class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-900/40 dark:file:text-emerald-300 hover:file:bg-emerald-100 dark:hover:file:bg-emerald-900/60 transition-colors cursor-pointer">
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Hanya file PDF dengan ukuran maksimal 5MB. Unggah jika ingin mengganti file yang sudah ada.</p>
                        @error('file')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200/60 dark:border-gray-700/60">
                        <a href="{{ route('curriculum.lesson-plans.index') }}"
                           class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/40 hover:-translate-y-0.5 transition-all duration-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
