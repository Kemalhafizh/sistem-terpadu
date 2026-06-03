<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
            {{ __('Mulai Diskusi Baru') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">
            
            <a href="{{ route('forum.index') }}" class="inline-flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Forum
            </a>

            <!-- Creation card -->
            <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
                
                <div class="border-b border-gray-100 dark:border-slate-800 pb-4">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Buat Topik Baru</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Isi formulir di bawah ini dengan lengkap untuk memulai diskusi akademik baru.</p>
                </div>

                <form action="{{ route('forum.threads.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div class="space-y-1.5">
                        <label for="title" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Judul Diskusi</label>
                        <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="Tuliskan judul/topik diskusi yang jelas..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all @error('title') border-red-500 @enderror">
                        @error('title')
                            <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Scope selection -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1.5">
                            <label for="scope_select" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Cakupan Visibilitas</label>
                            <select name="scope" id="scope_select" required class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-3 focus:border-indigo-500 transition-all">
                                <option value="school" {{ old('scope') === 'school' ? 'selected' : '' }}>Sekolah (Publik)</option>
                                <option value="class" {{ old('scope') === 'class' ? 'selected' : '' }}>Kelas Tertentu</option>
                                <option value="subject" {{ old('scope', 'class') === 'subject' ? 'selected' : '' }}>Mata Pelajaran</option>
                            </select>
                            @error('scope')
                                <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Class target (Conditional) -->
                        <div id="classroom_container" class="space-y-1.5 hidden">
                            <label for="classroom_id" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Pilih Kelas</label>
                            <select name="classroom_id" id="classroom_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-3 focus:border-indigo-500 transition-all">
                                @if(auth()->user()->role === 'student')
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}" selected>{{ $c->name }}</option>
                                    @endforeach
                                @else
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classrooms as $c)
                                        <option value="{{ $c->id }}" {{ old('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('classroom_id')
                                <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subject target (Conditional) -->
                        <div id="subject_container" class="space-y-1.5 hidden">
                            <label for="subject_id" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Mata Pelajaran</label>
                            <select name="subject_id" id="subject_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-3 focus:border-indigo-500 transition-all">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- E-learning Class target (Conditional for guru/admin if scope is subject) -->
                    @if(!auth()->user()->isParent() && $elearningClasses->isNotEmpty())
                        <div id="elearning_container" class="space-y-1.5 hidden">
                            <label for="elearning_class_id" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Hubungkan dengan Kelas Virtual LMS</label>
                            <select name="elearning_class_id" id="elearning_class_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-3 focus:border-indigo-500 transition-all">
                                <option value="">-- Tidak Dihubungkan --</option>
                                @foreach($elearningClasses as $ec)
                                    <option value="{{ $ec->id }}" {{ old('elearning_class_id') == $ec->id ? 'selected' : '' }}>{{ $ec->name }}</option>
                                @endforeach
                            </select>
                            @error('elearning_class_id')
                                <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Body / Editor -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="body" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Isi Diskusi</label>
                            <button type="button" id="preview_toggle" class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider hover:underline">Pratinjau Markdown</button>
                        </div>
                        
                        <div class="space-y-2">
                            <!-- Textarea -->
                            <textarea name="body" id="body" required rows="10" placeholder="Tuliskan isi diskusi Anda di sini. Anda dapat menggunakan format Markdown (misal: *tebal*, # Judul, dll) untuk merapikan tulisan..." class="w-full text-xs font-medium rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                            
                            <!-- Markdown Preview Container -->
                            <div id="preview_container" class="hidden min-h-[160px] p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs prose dark:prose-invert max-w-none overflow-y-auto leading-relaxed">
                                <span class="text-slate-400 italic">Menyiapkan pratinjau...</span>
                            </div>
                        </div>
                        @error('body')
                            <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- File Attachment -->
                    <div class="space-y-1.5">
                        <label for="attachment" class="text-xs font-bold text-gray-700 dark:text-slate-300 uppercase tracking-wider">Unggah Lampiran (Opsional)</label>
                        <input type="file" name="attachment" id="attachment" class="w-full text-xs font-semibold rounded-xl border border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-700 dark:text-slate-300 px-4 py-2.5 focus:indigo-500 transition-all">
                        <p class="text-[9px] font-semibold text-gray-400 dark:text-slate-500">Mendukung berkas: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, JPEG, ZIP, RAR (Maks. 10MB)</p>
                        @error('attachment')
                            <p class="text-[10px] font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Moderation options (Guru/Admin only) -->
                    @if(auth()->user()->isAdmin() || auth()->user()->role === 'guru')
                        <div class="bg-indigo-50/55 dark:bg-slate-950/55 p-4 rounded-2xl border border-indigo-100/50 dark:border-slate-800/80 space-y-3">
                            <h4 class="text-[10px] font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-widest">Opsi Moderasi Cepat</h4>
                            
                            <div class="flex flex-wrap gap-6">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 transition-all">
                                    <label for="is_pinned" class="text-xs font-bold text-gray-700 dark:text-slate-300 select-none cursor-pointer">Sematkan di Atas (Pin)</label>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_announcement" id="is_announcement" value="1" {{ old('is_announcement') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-slate-800 text-indigo-600 focus:ring-indigo-500 transition-all">
                                    <label for="is_announcement" class="text-xs font-bold text-gray-700 dark:text-slate-300 select-none cursor-pointer">Tandai sebagai Pengumuman</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Action buttons -->
                    <div class="pt-4 border-t border-gray-100 dark:border-slate-800 flex justify-end space-x-3">
                        <a href="{{ route('forum.index') }}" class="px-5 py-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-extrabold text-center hover:bg-slate-50 dark:hover:bg-slate-950/30 transition-all">
                            Batalkan
                        </a>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-extrabold shadow-md hover:-translate-y-0.5 transition-all">
                            Terbitkan Diskusi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Live Scope Control and Markdown Preview Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const scopeSelect = document.getElementById('scope_select');
            const classContainer = document.getElementById('classroom_container');
            const subjectContainer = document.getElementById('subject_container');
            const elearningContainer = document.getElementById('elearning_container');
            
            function handleScopeChange(value) {
                if (value === 'school') {
                    classContainer.classList.add('hidden');
                    subjectContainer.classList.add('hidden');
                    if(elearningContainer) elearningContainer.classList.add('hidden');
                } else if (value === 'class') {
                    classContainer.classList.remove('hidden');
                    subjectContainer.classList.add('hidden');
                    if(elearningContainer) elearningContainer.classList.add('hidden');
                } else if (value === 'subject') {
                    classContainer.classList.remove('hidden'); // mapel juga butuh relasi kelas
                    subjectContainer.classList.remove('hidden');
                    if(elearningContainer) elearningContainer.classList.remove('hidden');
                }
            }

            scopeSelect.addEventListener('change', (e) => {
                handleScopeChange(e.target.value);
            });

            // Trigger initial change based on old / default value
            handleScopeChange(scopeSelect.value);

            // Simple Markdown Previewer
            const bodyText = document.getElementById('body');
            const previewContainer = document.getElementById('preview_container');
            const previewToggle = document.getElementById('preview_toggle');
            
            let isPreview = false;

            previewToggle.addEventListener('click', () => {
                isPreview = !isPreview;
                if (isPreview) {
                    bodyText.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                    previewToggle.textContent = 'Edit Konten';
                    
                    // Simple replacement mapping for bold, italic, lists, and headings
                    let raw = bodyText.value;
                    if (!raw.trim()) {
                        previewContainer.innerHTML = '<span class="text-slate-400 italic">Konten kosong.</span>';
                        return;
                    }

                    let parsed = raw
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;")
                        .replace(/^### (.*$)/gim, '<h5 class="font-extrabold text-sm text-slate-800 dark:text-slate-200 mt-2">$1</h5>')
                        .replace(/^## (.*$)/gim, '<h4 class="font-black text-base text-slate-800 dark:text-slate-200 mt-3">$1</h4>')
                        .replace(/^# (.*$)/gim, '<h3 class="font-black text-lg text-slate-800 dark:text-slate-200 mt-4">$1</h3>')
                        .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                        .replace(/\*(.*)\*/gim, '<em>$1</em>')
                        .replace(/^\s*\-\s(.*$)/gim, '<li class="list-disc ml-4">$1</li>')
                        .replace(/\n$/gim, '<br />')
                        .replace(/\n/g, '<br />');

                    previewContainer.innerHTML = parsed;
                } else {
                    bodyText.classList.remove('hidden');
                    previewContainer.classList.add('hidden');
                    previewToggle.textContent = 'Pratinjau Markdown';
                }
            });
        });
    </script>
</x-app-layout>
