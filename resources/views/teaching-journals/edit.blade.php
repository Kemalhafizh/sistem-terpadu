<x-app-layout>
    <x-slot name="title">Edit Jurnal Mengajar</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('teaching-journals.index') }}" class="p-2 bg-gray-150 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 rounded-xl transition-all">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    ✏️ Edit Jurnal Mengajar Harian
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sunting data jurnal mengajar harian sebelum diajukan/dikunci.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-200/60 dark:border-gray-700/60 shadow-xl">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 rounded-2xl border border-red-200 dark:border-red-900/50">
                <ul class="list-disc pl-5 text-xs font-semibold space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teaching-journals.update', $teachingJournal->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Tanggal Mengajar <span class="text-red-500">*</span></label>
                    <input type="date" name="teaching_date" required max="{{ date('Y-m-d') }}" value="{{ old('teaching_date', $teachingJournal->teaching_date->format('Y-m-d')) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Jam Ke pelajaran</label>
                    <input type="number" name="jam_ke" min="1" max="12" placeholder="Contoh: 1" value="{{ old('jam_ke', $teachingJournal->jam_ke) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                    <select name="classroom_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                        <option value="">Pilih Kelas</option>
                        @foreach($classrooms as $c)
                            <option value="{{ $c->id }}" {{ old('classroom_id', $teachingJournal->classroom_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="subject_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                        <option value="">Pilih Mapel</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ old('subject_id', $teachingJournal->subject_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Topik / Materi Pokok <span class="text-red-500">*</span></label>
                <input type="text" name="topic" required placeholder="Contoh: Aljabar Linier & Persamaan Kuadrat" value="{{ old('topic', $teachingJournal->topic) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Aktivitas & Uraian Pembelajaran</label>
                <textarea name="learning_activity" placeholder="Tuliskan detail aktivitas di kelas..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none" rows="4">{{ old('learning_activity', $teachingJournal->learning_activity) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Metode Pembelajaran</label>
                    <select name="learning_method" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                        <option value="Ceramah" {{ old('learning_method', $teachingJournal->learning_method) === 'Ceramah' ? 'selected' : '' }}>Ceramah</option>
                        <option value="Diskusi Kelompok" {{ old('learning_method', $teachingJournal->learning_method) === 'Diskusi Kelompok' ? 'selected' : '' }}>Diskusi Kelompok</option>
                        <option value="Praktikum" {{ old('learning_method', $teachingJournal->learning_method) === 'Praktikum' ? 'selected' : '' }}>Praktikum / Eksperimen</option>
                        <option value="Tanya Jawab" {{ old('learning_method', $teachingJournal->learning_method) === 'Tanya Jawab' ? 'selected' : '' }}>Tanya Jawab</option>
                        <option value="Project Based" {{ old('learning_method', $teachingJournal->learning_method) === 'Project Based' ? 'selected' : '' }}>Project Based Learning</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Siswa Hadir</label>
                    <input type="number" name="students_present" min="0" placeholder="0" value="{{ old('students_present', $teachingJournal->students_present) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Siswa Absen/Alpa</label>
                    <input type="number" name="students_absent" min="0" placeholder="0" value="{{ old('students_absent', $teachingJournal->students_absent) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Hambatan / Kendala Kelas</label>
                <input type="text" name="obstacles" placeholder="Tulis hambatan jika ada..." value="{{ old('obstacles', $teachingJournal->obstacles) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Catatan Khusus Sesi / Rencana Tindak Lanjut</label>
                <textarea name="notes" placeholder="Catatan tambahan..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none" rows="2">{{ old('notes', $teachingJournal->notes) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Lampiran Saat Ini</label>
                @if($teachingJournal->attachment_path)
                    <div class="mb-3 flex items-center gap-2 p-3 bg-gray-50 dark:bg-gray-950 rounded-xl border border-gray-200/50 dark:border-gray-700/50 w-fit">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <a href="{{ asset('storage/' . $teachingJournal->attachment_path) }}" target="_blank" class="text-xs font-bold text-primary-600 hover:underline">Unduh Lampiran Sekarang</a>
                    </div>
                @else
                    <p class="text-xs text-gray-400 italic mb-3">Belum ada berkas lampiran terunggah.</p>
                @endif
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Ganti Lampiran Foto / Dokumen Ajar (Maks 2MB)</label>
                <input type="file" name="attachment" class="w-full bg-transparent text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-primary-50 file:text-primary-700 dark:file:bg-gray-700 dark:file:text-gray-300">
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-150 dark:border-gray-700">
                <a href="{{ route('teaching-journals.index') }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider transition-all">
                    Batal
                </a>
                <button type="submit" name="action" value="draft" class="bg-gray-800 dark:bg-gray-200 hover:bg-gray-900 dark:hover:bg-white text-white dark:text-gray-950 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider transition-all">
                    Simpan Draft
                </button>
                <button type="submit" name="action" value="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-xl text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all">
                    Kirim Laporan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
