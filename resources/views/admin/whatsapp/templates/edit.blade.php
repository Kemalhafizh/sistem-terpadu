<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.whatsapp.templates.index') }}" class="p-2 rounded-xl bg-white dark:bg-gray-800 text-gray-500 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-teal-50 dark:hover:bg-teal-950/20 border border-gray-100 dark:border-gray-700 shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Edit Template Pesan') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Perbarui detail template notifikasi WhatsApp yang diubah.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Main Edit Form Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Left Panel: Form Input (2 Columns wide) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <form action="{{ route('admin.whatsapp.templates.update', $template->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <!-- Name Input -->
                        <div>
                            <label for="name" class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Nama Template</label>
                            <input type="text" id="name" name="name" required placeholder="Contoh: Pemberitahuan Tagihan SPP Bulanan" value="{{ old('name', $template->name) }}" class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 transition-colors">
                            @error('name')
                                <p class="text-rose-600 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category Select -->
                        <div>
                            <label for="category" class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Kategori Template</label>
                            <select id="category" name="category" required class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 transition-colors">
                                <option value="" disabled>Pilih Kategori...</option>
                                <option value="keuangan" {{ old('category', $template->category) === 'keuangan' ? 'selected' : '' }}>Keuangan (SPP, Iuran, Invoice)</option>
                                <option value="akademik" {{ old('category', $template->category) === 'akademik' ? 'selected' : '' }}>Akademik (Nilai, KKM, Ujian)</option>
                                <option value="keislaman" {{ old('category', $template->category) === 'keislaman' ? 'selected' : '' }}>Keislaman (Mutaba'ah, Tahfidz)</option>
                                <option value="kegiatan" {{ old('category', $template->category) === 'kegiatan' ? 'selected' : '' }}>Kegiatan (Agenda OSIS, Rapat Wali Murid)</option>
                                <option value="umum" {{ old('category', $template->category) === 'umum' ? 'selected' : '' }}>Umum (Pengumuman Sekolah, Broadcast Kustom)</option>
                            </select>
                            @error('category')
                                <p class="text-rose-600 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content Textarea -->
                        <div>
                            <label for="content" class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2">Isi Pesan Template</label>
                            <textarea id="content" name="content" rows="8" required placeholder="Tulis isi pesan WhatsApp di sini..." class="w-full text-sm bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 font-mono transition-colors">{{ old('content', $template->content) }}</textarea>
                            @error('content')
                                <p class="text-rose-600 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Active Toggle Checkbox -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div>
                                <label for="is_active" class="block text-xs font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status Aktif</label>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">Nonaktifkan untuk menyembunyikan template ini dari pilihan pengiriman siaran.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="is_active" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-teal-600"></div>
                            </label>
                        </div>

                        <!-- Placeholder Tag Helper Section -->
                        <div>
                            <span class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">Klik Variabel Penampung untuk Menyisipkan</span>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $placeholders = [
                                        '{nama_siswa}' => 'Nama Siswa',
                                        '{nama_orang_tua}' => 'Nama Orang Tua',
                                        '{kelas}' => 'Kelas',
                                        '{tagihan}' => 'Jumlah Tagihan',
                                        '{batas_waktu}' => 'Batas Waktu',
                                        '{nilai}' => 'Nilai Siswa',
                                        '{kkm}' => 'Nilai KKM',
                                        '{mata_pelajaran}' => 'Mata Pelajaran',
                                        '{keluhan}' => 'Keluhan UKS',
                                        '{diagnosa}' => 'Diagnosa Medis',
                                        '{penanganan}' => 'Penanganan UKS',
                                        '{gejala}' => 'Gejala Sakit',
                                        '{disposisi}' => 'Disposisi Rujukan'
                                    ];
                                @endphp
                                @foreach($placeholders as $tag => $label)
                                    <button type="button" onclick="insertPlaceholder('{{ $tag }}')" class="px-3 py-1.5 bg-gray-50 hover:bg-teal-50 dark:bg-gray-900 dark:hover:bg-teal-950/40 text-gray-600 hover:text-teal-700 dark:text-gray-400 dark:hover:text-teal-300 rounded-xl font-mono text-[11px] font-bold border border-gray-200 dark:border-gray-700 hover:border-teal-200 dark:hover:border-teal-900 transition-all hover:scale-[1.02]" title="{{ $label }}">
                                        {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 block mt-2">Sistem akan mengekstrak otomatis tag variabel di atas untuk diisi data aktual saat pengiriman.</span>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-5 py-3 bg-gray-50 hover:bg-gray-100 dark:bg-gray-900 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl text-xs font-black uppercase tracking-wider transition-all">
                                Batal
                            </a>
                            <button type="submit" class="px-5 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-500/20 hover:-translate-y-0.5">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Panel: Live Mobile Preview (1 Column wide) -->
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                            Live Simulator Preview
                        </h3>
                        
                        <!-- Simulated Mobile Screen / WhatsApp Bubble -->
                        <div class="bg-[#efeae2] dark:bg-slate-950/60 rounded-2xl border border-gray-200 dark:border-gray-800/80 p-4 min-h-[300px] flex flex-col justify-end relative shadow-inner overflow-hidden" 
                             style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-blend-mode: overlay; background-size: cover;">
                            
                            <div class="bg-white dark:bg-[#075e54]/80 rounded-2xl rounded-tl-none p-4 max-w-[90%] self-start shadow-sm text-xs text-gray-800 dark:text-slate-100 relative mb-4 border border-gray-100/50 dark:border-emerald-800/20">
                                <!-- Chat triangle tail -->
                                <span class="absolute top-0 -left-2 text-white dark:text-[#075e54]/80">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 10 10"><path d="M10 0v10L0 0z"/></svg>
                                </span>
                                
                                <div id="preview-text" class="whitespace-pre-line leading-relaxed italic text-gray-400 dark:text-slate-400">
                                    Tulis isi template untuk melihat pratinjau pesan di sini...
                                </div>
                                <span class="block text-[9px] text-right text-gray-400 dark:text-emerald-200/50 mt-2 font-medium">
                                    {{ now()->format('H:i') }}
                                </span>
                            </div>

                        </div>
                        
                        <!-- Formatting Guidelines -->
                        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 text-[11px] text-gray-500 space-y-1.5 leading-relaxed">
                            <span class="block font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest text-[9px] mb-1">Tips Format Markdown WhatsApp:</span>
                            <p><strong class="font-bold text-gray-800 dark:text-white">*teks tebal*</strong> &rarr; menghasilkan <strong>teks tebal</strong></p>
                            <p><strong class="font-bold text-gray-800 dark:text-white">_teks miring_</strong> &rarr; menghasilkan <em>teks miring</em></p>
                            <p><strong class="font-bold text-gray-800 dark:text-white">~teks dicoret~</strong> &rarr; menghasilkan <del>teks dicoret</del></p>
                            <p><strong class="font-bold text-gray-800 dark:text-white">```monospaced```</strong> &rarr; menghasilkan <code class="font-mono bg-gray-200 dark:bg-gray-800 px-1 py-0.5 rounded text-[10px]">monospaced</code></p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Scripts for dynamic interaction -->
    <script>
        // Insert placeholder tag at current cursor position in textarea
        function insertPlaceholder(placeholder) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const before = text.substring(0, start);
            const after  = text.substring(end, text.length);
            textarea.value = before + placeholder + after;
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + placeholder.length;
            
            // Dispatch input event to refresh preview
            textarea.dispatchEvent(new Event('input'));
        }

        // Live text parser for preview
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('content');
            const preview = document.getElementById('preview-text');
            
            const updatePreview = () => {
                let val = textarea.value;
                if (val.trim().length === 0) {
                    preview.textContent = "Tulis isi template untuk melihat pratinjau pesan di sini...";
                    preview.className = "whitespace-pre-line leading-relaxed italic text-gray-400 dark:text-slate-400";
                } else {
                    // Escape HTML characters
                    let escaped = val
                        .replace(/&/g, "&amp;")
                        .replace(/</g, "&lt;")
                        .replace(/>/g, "&gt;");
                    
                    // Format bold (*text*)
                    escaped = escaped.replace(/\*(.*?)\*/g, '<strong class="font-black">$1</strong>');
                    
                    // Format italic (_text_)
                    escaped = escaped.replace(/_(.*?)_/g, '<em class="italic text-slate-200">$1</em>');

                    // Format strike (~text~)
                    escaped = escaped.replace(/~(.*?)~/g, '<del class="line-through opacity-70">$1</del>');

                    // Format monospaced (```text```)
                    escaped = escaped.replace(/```(.*?)```/g, '<code class="font-mono bg-emerald-900/50 dark:bg-emerald-950 px-1 py-0.5 rounded text-[10px]">$1</code>');
                    
                    // Highlight custom template placeholders like {nama_siswa}
                    escaped = escaped.replace(/\{([a-zA-Z0-9_]+)\}/g, '<span class="inline-block px-1.5 py-0.5 rounded bg-teal-600 dark:bg-teal-900 text-white dark:text-teal-300 font-mono text-[9px] font-bold border border-teal-400 dark:border-teal-800 shadow-sm">$1</span>');
                    
                    preview.innerHTML = escaped;
                    preview.className = "whitespace-pre-line leading-relaxed text-gray-800 dark:text-slate-100 font-medium";
                }
            };
            
            textarea.addEventListener('input', updatePreview);
            // Run initially
            updatePreview();
        });
    </script>
</x-app-layout>
