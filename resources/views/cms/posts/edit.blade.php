<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Artikel / Berita') }}
            </h2>
            <a href="{{ route('cms.posts.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-250 text-gray-700 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-150 rounded-xl overflow-hidden p-6 md:p-8">
                <form action="{{ route('cms.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Main form elements (column 1 & 2) -->
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <label for="title" class="block text-sm font-bold text-gray-700">Judul Artikel</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $post->title) }}" required
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                       placeholder="Contoh: Siswa SMA Terpadu Meraih Emas Olimpiade Fisika">
                                @error('title')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-bold text-gray-700">Slug URL</label>
                                <div class="mt-1 flex rounded-xl shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-xs font-mono">
                                        /berita/
                                    </span>
                                    <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug) }}" required
                                           class="flex-1 block w-full rounded-r-xl border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-mono"
                                           placeholder="siswa-sma-terpadu-meraih-emas">
                                </div>
                                @error('slug')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="excerpt" class="block text-sm font-bold text-gray-700">Kutipan / Excerpt</label>
                                <textarea name="excerpt" id="excerpt" rows="3"
                                          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                          placeholder="Tulis ringkasan singkat artikel ini untuk pratinjau di halaman beranda...">{{ old('excerpt', $post->excerpt) }}</textarea>
                                <p class="mt-1 text-xs text-gray-400">Ringkasan singkat 2-3 kalimat (maksimal 500 karakter).</p>
                                @error('excerpt')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="body" class="block text-sm font-bold text-gray-700">Isi Artikel / Konten Utama</label>
                                <textarea name="body" id="body" rows="12" required
                                          class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                          placeholder="Tulis konten lengkap berita atau artikel di sini...">{{ old('body', $post->body) }}</textarea>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Sidebar parameters (column 3) -->
                        <div class="space-y-6 bg-gray-50 p-5 rounded-xl border border-gray-150 h-fit">
                            <div>
                                <label for="category" class="block text-sm font-bold text-gray-700">Kategori</label>
                                <select name="category" id="category" required
                                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-bold">
                                    <option value="berita" {{ old('category', $post->category) == 'berita' ? 'selected' : '' }}>Berita Sekolah</option>
                                    <option value="prestasi" {{ old('category', $post->category) == 'prestasi' ? 'selected' : '' }}>Prestasi</option>
                                    <option value="kegiatan" {{ old('category', $post->category) == 'kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                                    <option value="artikel" {{ old('category', $post->category) == 'artikel' ? 'selected' : '' }}>Artikel Guru & Staf</option>
                                </select>
                                @error('category')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Gambar Sampul (Cover)</label>
                                <div class="space-y-3">
                                    <!-- Image preview slot -->
                                    <div id="image-preview-wrapper" class="{{ $post->cover_image ? '' : 'hidden' }} w-full aspect-video rounded-lg overflow-hidden border border-gray-200 bg-white relative">
                                        <img id="image-preview" src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : '#' }}" class="w-full h-full object-cover" alt="Pratinjau Cover">
                                        <button type="button" id="remove-image-btn" class="absolute top-2 right-2 p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full transition-all shadow">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                    
                                    <label class="{{ $post->cover_image ? 'hidden' : '' }} w-full flex flex-col items-center justify-center px-4 py-6 bg-white text-gray-400 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-500 hover:text-primary-500 transition-all cursor-pointer text-center" id="upload-placeholder">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="mt-2 text-xs font-semibold">Pilih File Gambar</span>
                                        <span class="text-[10px] text-gray-400 mt-0.5">JPG, PNG, GIF maks 2MB</span>
                                        <input type="file" name="cover_image" id="cover_image" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                @error('cover_image')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 pt-4 space-y-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_published" id="is_published" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-bold text-gray-700">Terbitkan Langsung</span>
                                </label>

                                <div id="publish-date-wrapper" class="space-y-1">
                                    <label for="published_at" class="block text-xs font-bold text-gray-500">Tanggal Publikasi</label>
                                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-xs font-medium">
                                    <p class="text-[10px] text-gray-400 mt-1">Kosongkan untuk otomatis terbit di waktu sekarang.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex items-center justify-end space-x-2">
                        <a href="{{ route('cms.posts.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all">
                            Perbarui Artikel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Slug Generator
            const titleInput = document.getElementById('title');
            const slugInput = document.getElementById('slug');

            titleInput.addEventListener('input', function () {
                if (slugInput.dataset.manual === 'true') return;
                
                const titleValue = titleInput.value;
                const slugified = titleValue
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                
                slugInput.value = slugified;
            });

            slugInput.addEventListener('input', function() {
                slugInput.dataset.manual = 'true';
            });

            // Cover Image Preview
            const coverInput = document.getElementById('cover_image');
            const previewWrapper = document.getElementById('image-preview-wrapper');
            const previewImage = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            const removeBtn = document.getElementById('remove-image-btn');

            coverInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.setAttribute('src', e.target.result);
                        previewWrapper.classList.remove('hidden');
                        placeholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                }
            });

            removeBtn.addEventListener('click', function () {
                coverInput.value = '';
                previewImage.setAttribute('src', '#');
                previewWrapper.classList.add('hidden');
                placeholder.classList.remove('hidden');
            });
        });
    </script>
</x-app-layout>
