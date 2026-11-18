<x-app-layout>
    <x-slot name="title">Isi Jurnal Mengajar Baru</x-slot>

    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('teaching-journals.index') }}" class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-xl transition-all">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Pengisian Jurnal Mengajar Harian
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Catat detail aktivitas tatap muka pembelajaran kelas, metode, materi, dan kendala hari ini.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="journalForm()">
        <!-- Panel Kiri: Form Input -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-200/60 dark:border-gray-700/60 shadow-xl">
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 rounded-2xl border border-red-200 dark:border-red-900/50">
                    <ul class="list-disc pl-5 text-xs font-semibold space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="teaching-journal-form" action="{{ route('teaching-journals.store') }}" method="POST" enctype="multipart/form-data" data-offline-sync="true" data-type-name="Jurnal Mengajar" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Tanggal Mengajar <span class="text-red-500">*</span></label>
                        <input type="date" name="teaching_date" required max="{{ date('Y-m-d') }}" value="{{ old('teaching_date', date('Y-m-d')) }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Jam Ke pelajaran</label>
                        <input type="number" name="jam_ke" id="jam_ke" min="1" max="12" placeholder="Contoh: 1" value="{{ old('jam_ke') }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                        <select name="classroom_id" id="classroom_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                            <option value="">Pilih Kelas</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}" {{ old('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select name="subject_id" id="subject_id" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                            <option value="">Pilih Mapel</option>
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Topik / Materi Pokok <span class="text-red-500">*</span></label>
                    <input type="text" name="topic" required placeholder="Contoh: Aljabar Linier & Persamaan Kuadrat" value="{{ old('topic') }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Aktivitas & Uraian Pembelajaran</label>
                    <textarea name="learning_activity" placeholder="Tuliskan detail aktivitas di kelas (misal: pengerjaan latihan soal kelompok, praktikum di lab...)" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none" rows="4">{{ old('learning_activity') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Metode Pembelajaran</label>
                        <select name="learning_method" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                            <option value="Ceramah" {{ old('learning_method') === 'Ceramah' ? 'selected' : '' }}>Ceramah</option>
                            <option value="Diskusi Kelompok" {{ old('learning_method') === 'Diskusi Kelompok' ? 'selected' : '' }}>Diskusi Kelompok</option>
                            <option value="Praktikum" {{ old('learning_method') === 'Praktikum' ? 'selected' : '' }}>Praktikum / Eksperimen</option>
                            <option value="Tanya Jawab" {{ old('learning_method') === 'Tanya Jawab' ? 'selected' : '' }}>Tanya Jawab</option>
                            <option value="Project Based" {{ old('learning_method') === 'Project Based' ? 'selected' : '' }}>Project Based Learning</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Siswa Hadir</label>
                        <input type="number" name="students_present" min="0" placeholder="0" value="{{ old('students_present') }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Siswa Absen/Alpa</label>
                        <input type="number" name="students_absent" min="0" placeholder="0" value="{{ old('students_absent') }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Hambatan / Kendala Kelas</label>
                    <input type="text" name="obstacles" placeholder="Tulis hambatan jika ada (misal: 3 siswa mengantuk, proyektor mati...)" value="{{ old('obstacles') }}" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Catatan Khusus Sesi / Rencana Tindak Lanjut</label>
                    <textarea name="notes" placeholder="Catatan tambahan (misal: perlu remidi minggu depan untuk materi ini...)" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-primary-500 focus:outline-none" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">Lampiran Foto / Dokumen Ajar (Maks 2MB)</label>
                    <div class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-200 dark:border-gray-700 border-dashed rounded-2xl bg-gray-50 dark:bg-gray-900">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h16a4 4 0 004-4V12a4 4 0 00-4-4z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M32 28l-8-8-8 8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-bold text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Unggah Berkas</span>
                                    <input id="file-upload" name="attachment" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">atau seret dan lepas berkas disini</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PDF, PNG, JPG, JPEG hingga 2MB</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('teaching-journals.index') }}" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-xl text-sm font-bold uppercase tracking-wider transition-all">
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

        <!-- Panel Kanan: Rekomendasi Jadwal Hari Ini -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between h-fit gap-6">
            <div>
                <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span></span> Jadwal Anda Hari Ini
                </h3>
                <div class="space-y-3">
                    @forelse($suggestedSchedule ?? [] as $sched)
                    <div @click="applySchedule({{ $sched->classroom_id }}, {{ $sched->subject_id }}, {{ $sched->session_start }})" class="group p-4 rounded-2xl bg-gray-50 hover:bg-primary-50 dark:bg-gray-900 dark:hover:bg-gray-950/40 border border-gray-200/40 hover:border-primary-400 dark:border-gray-700 dark:hover:border-primary-800 cursor-pointer transition-all duration-300 transform hover:-translate-y-0.5">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[10px] font-bold text-primary-600 dark:text-primary-400 bg-primary-100/50 dark:bg-primary-950/20 px-2 py-0.5 rounded">Sesi Ke-{{ $sched->session_start }} s/d {{ $sched->session_end }}</span>
                            <span class="text-[10px] font-extrabold bg-gray-200 dark:bg-gray-800 px-2 py-0.5 rounded text-gray-800 dark:text-gray-300">{{ $sched->classroom->name }}</span>
                        </div>
                        <div class="font-bold text-sm text-gray-800 dark:text-gray-100 group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors">{{ $sched->subject->name }}</div>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 flex items-center gap-1 font-semibold">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Klik kartu untuk isi form otomatis
                        </p>
                    </div>
                    @empty
                    <p class="text-xs font-medium text-gray-500 italic py-4">Tidak ada jadwal mengajar formal terdaftar untuk Anda hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function journalForm() {
            return {
                applySchedule(classroomId, subjectId, startSession) {
                    document.getElementById('classroom_id').value = classroomId;
                    document.getElementById('subject_id').value = subjectId;
                    document.getElementById('jam_ke').value = startSession;
                    if (window.showToast) {
                        window.showToast('success', 'Form telah diisi otomatis berdasarkan jadwal!');
                    } else {
                        alert('Form telah diisi otomatis berdasarkan jadwal!');
                    }
                }
            }
        }
    </script>
</x-app-layout>
