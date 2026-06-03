<x-app-layout>
    <x-slot name="title">Buat Survei Baru</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Survei Digital', 'url' => route('surveys.index')],
            ['label' => 'Buat Survei', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('surveys.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <div>
                <h1 class="text-3xl font-black tracking-tight text-slate-800 dark:text-white">
                    Buat Survei & Kuesioner Baru
                </h1>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-1">
                    Buat kuesioner dinamis untuk evaluasi sekolah dengan formulir kustom.
                </p>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('surveys.store') }}" method="POST" class="space-y-8" 
          x-data="surveyBuilder()">
        @csrf
        
        <!-- Survey Meta & Configuration -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Konfigurasi & Informasi Utama</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Title & Description -->
                <div class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Judul Survei <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}"
                               placeholder="Contoh: Survei Evaluasi Pembelajaran Semester Ganjil"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold placeholder-slate-400">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Deskripsi / Petunjuk Pengisian</label>
                        <textarea name="description" id="description" rows="4"
                                  placeholder="Berikan petunjuk singkat pengisian kuesioner kepada responden..."
                                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold placeholder-slate-400">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Parameters -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="target_role" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Target Responden (Role)</label>
                        <select name="target_role" id="target_role" x-model="targetRole"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold">
                            <option value="">Semua Peran (Publik Internal)</option>
                            <option value="student">Siswa</option>
                            <option value="parent">Orang Tua Siswa</option>
                            <option value="guru">Guru / Tenaga Pendidik</option>
                            <option value="admin">Staf / Administrator</option>
                        </select>
                    </div>

                    <div x-show="targetRole === 'student'" x-transition>
                        <label for="target_classroom_id" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Target Kelas Spesifik</label>
                        <select name="target_classroom_id" id="target_classroom_id"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold">
                            <option value="">Semua Kelas</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="is_anonymous" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Sifat Identitas <span class="text-rose-500">*</span></label>
                        <select name="is_anonymous" id="is_anonymous"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold">
                            <option value="1" {{ old('is_anonymous', '1') == '1' ? 'selected' : '' }}>Anonim (Identitas Dirahasiakan - PDP Protected)</option>
                            <option value="0" {{ old('is_anonymous') == '0' ? 'selected' : '' }}>Terbuka (Identitas Responden Terlihat)</option>
                        </select>
                    </div>

                    <div>
                        <label for="max_responses" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Kuota Maksimum Respons</label>
                        <input type="number" name="max_responses" id="max_responses" min="1" value="{{ old('max_responses') }}"
                               placeholder="Tak terbatas jika kosong"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold placeholder-slate-400">
                    </div>

                    <div>
                        <label for="start_date" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Tanggal Mulai Aktif</label>
                        <input type="datetime-local" name="start_date" id="start_date" value="{{ old('start_date') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-black text-slate-700 dark:text-slate-300 mb-2">Tanggal Batas Akhir (Deadline)</label>
                        <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}"
                               class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold">
                    </div>

                    <div class="col-span-full pt-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="hidden" name="send_notification" value="0">
                            <input type="checkbox" name="send_notification" value="1" {{ old('send_notification', '1') == '1' ? 'checked' : '' }}
                                   class="rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 w-5 h-5 transition-all">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                Kirim Notifikasi Broadcast WhatsApp Otomatis ke Target saat Survei Aktif
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Builder Section -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200/50 dark:border-slate-700/50">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Daftar Pertanyaan Kuesioner</h2>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Definisikan butir kuesioner Anda di bawah ini secara fleksibel.</p>
                    </div>
                </div>
                <button type="button" @click="addQuestion()"
                        class="inline-flex items-center px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-500/10">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Pertanyaan
                </button>
            </div>

            <!-- Empty State of Questions -->
            <template x-if="questions.length === 0">
                <div class="text-center py-12 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800">
                    <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">Belum ada pertanyaan kuesioner.</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Klik tombol 'Tambah Pertanyaan' di atas untuk mulai menyusun.</p>
                </div>
            </template>

            <!-- Dynamic Question List -->
            <div class="space-y-6">
                <template x-for="(q, qIndex) in questions" :key="q.tempId">
                    <div class="relative bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-5 space-y-4 hover:border-emerald-500/30 transition-all">
                        
                        <!-- Question Header Controls -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 text-xs font-black mr-2" x-text="qIndex + 1"></span>
                                Pertanyaan
                            </span>

                            <div class="flex items-center space-x-2">
                                <!-- Reorder Buttons -->
                                <button type="button" @click="moveUp(qIndex)" :disabled="qIndex === 0"
                                        class="p-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button type="button" @click="moveDown(qIndex)" :disabled="qIndex === questions.length - 1"
                                        class="p-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 disabled:opacity-30 disabled:cursor-not-allowed hover:bg-slate-100 dark:hover:bg-slate-700 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <!-- Delete Button -->
                                <button type="button" @click="removeQuestion(qIndex)"
                                        class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/20 text-rose-600 hover:bg-rose-100 dark:hover:bg-rose-900/30 transition-all ml-4">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Question Inputs -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Question Text -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Pertanyaan <span class="text-rose-500">*</span></label>
                                <input type="text" :name="`questions[${qIndex}][question_text]`" required x-model="q.question_text"
                                       placeholder="Tuliskan pertanyaan kuesioner..."
                                       class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm">
                            </div>

                            <!-- Question Type -->
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Tipe Respon <span class="text-rose-500">*</span></label>
                                <select :name="`questions[${qIndex}][type]`" x-model="q.type" @change="onTypeChange(qIndex)"
                                        class="w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-sm">
                                    <option value="text">Teks Singkat (Text)</option>
                                    <option value="textarea">Esai Panjang (Textarea)</option>
                                    <option value="radio">Pilihan Tunggal (Radio)</option>
                                    <option value="checkbox">Pilihan Berganda (Checkbox)</option>
                                    <option value="select">Dropdown Opsi (Select)</option>
                                    <option value="scale">Rating Skala Likert (Scale)</option>
                                    <option value="date">Input Tanggal (Date)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Conditional Settings based on Type -->
                        
                        <!-- 1. Options Builder (Radio, Checkbox, Select) -->
                        <div x-show="['radio', 'checkbox', 'select'].includes(q.type)" x-transition class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200/50 dark:border-slate-700/50 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700">
                                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300">Pilihan Opsi Respon</span>
                                <button type="button" @click="addOption(qIndex)"
                                        class="inline-flex items-center text-[11px] font-black uppercase text-emerald-600 dark:text-emerald-400 hover:text-emerald-700">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Pilihan Opsi
                                </button>
                            </div>

                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(opt, optIndex) in q.options" :key="optIndex">
                                    <div class="flex items-center space-x-2">
                                        <input type="text" :name="`questions[${qIndex}][options][]`" required x-model="q.options[optIndex]"
                                               placeholder="Contoh: Sangat Setuju"
                                               class="flex-1 px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all font-semibold text-xs">
                                        
                                        <button type="button" @click="removeOption(qIndex, optIndex)" :disabled="q.options.length <= 1"
                                                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 disabled:opacity-30 disabled:cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- 2. Scale Likert Settings -->
                        <div x-show="q.type === 'scale'" x-transition class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200/50 dark:border-slate-700/50 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Skala Minimal <span class="text-rose-500">*</span></label>
                                <input type="number" :name="`questions[${qIndex}][scale_min]`" x-model.number="q.scale_min" min="0" max="5"
                                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Skala Maksimal <span class="text-rose-500">*</span></label>
                                <input type="number" :name="`questions[${qIndex}][scale_max]`" x-model.number="q.scale_max" min="1" max="10"
                                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white text-xs font-bold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Label Minimal (Kiri)</label>
                                <input type="text" :name="`questions[${qIndex}][scale_min_label]`" x-model="q.scale_min_label" placeholder="Contoh: Sangat Tidak Puas"
                                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Label Maksimal (Kanan)</label>
                                <input type="text" :name="`questions[${qIndex}][scale_max_label]`" x-model="q.scale_max_label" placeholder="Contoh: Sangat Puas"
                                       class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white text-xs font-semibold">
                            </div>
                        </div>

                        <!-- Footer Toggle controls -->
                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="hidden" :name="`questions[${qIndex}][is_required]`" value="0">
                                <input type="checkbox" :name="`questions[${qIndex}][is_required]`" value="1" x-model="q.is_required"
                                       class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 dark:border-slate-700 w-4.5 h-4.5">
                                <span class="text-xs font-extrabold text-slate-600 dark:text-slate-400">Pertanyaan Wajib Dijawab</span>
                            </label>
                        </div>

                    </div>
                </template>
            </div>
        </div>

        <!-- Form Submission Button -->
        <div class="flex items-center justify-end space-x-4 bg-white/80 dark:bg-slate-800/80 backdrop-blur-md border border-slate-200/50 dark:border-slate-700/50 rounded-3xl p-6 shadow-xl">
            <a href="{{ route('surveys.index') }}" 
               class="px-6 py-3 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl text-sm font-extrabold hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-all">
                Batal
            </a>
            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-2xl text-sm font-black shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all border border-emerald-400/20">
                Simpan Sebagai Draft
            </button>
        </div>

    </form>

    <script>
        function surveyBuilder() {
            return {
                targetRole: '{{ old('target_role', '') }}',
                questions: [],
                
                init() {
                    // Populate old input questions if any or initialize with 1 default question
                    const oldQuestions = @json(old('questions', []));
                    if (oldQuestions && oldQuestions.length > 0) {
                        this.questions = oldQuestions.map((q, idx) => ({
                            tempId: Date.now() + idx + Math.random(),
                            question_text: q.question_text || '',
                            type: q.type || 'text',
                            options: q.options || [''],
                            is_required: q.is_required == '1',
                            scale_min: q.scale_min !== undefined ? q.scale_min : 1,
                            scale_max: q.scale_max !== undefined ? q.scale_max : 5,
                            scale_min_label: q.scale_min_label || '',
                            scale_max_label: q.scale_max_label || '',
                        }));
                    } else {
                        this.addQuestion();
                    }
                },

                addQuestion() {
                    this.questions.push({
                        tempId: Date.now() + Math.random(),
                        question_text: '',
                        type: 'text',
                        options: [''],
                        is_required: true,
                        scale_min: 1,
                        scale_max: 5,
                        scale_min_label: '',
                        scale_max_label: ''
                    });
                },

                removeQuestion(index) {
                    this.questions.splice(index, 1);
                },

                onTypeChange(index) {
                    const q = this.questions[index];
                    if (['radio', 'checkbox', 'select'].includes(q.type) && (!q.options || q.options.length === 0)) {
                        q.options = ['Opsi 1'];
                    }
                },

                addOption(qIndex) {
                    this.questions[qIndex].options.push('');
                },

                removeOption(qIndex, optIndex) {
                    this.questions[qIndex].options.splice(optIndex, 1);
                },

                moveUp(index) {
                    if (index > 0) {
                        const temp = this.questions[index];
                        this.questions[index] = this.questions[index - 1];
                        this.questions[index - 1] = temp;
                    }
                },

                moveDown(index) {
                    if (index < this.questions.length - 1) {
                        const temp = this.questions[index];
                        this.questions[index] = this.questions[index + 1];
                        this.questions[index + 1] = temp;
                    }
                }
            }
        }
    </script>
</x-app-layout>
