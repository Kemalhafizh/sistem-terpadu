<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Halaman Statis') }}
            </h2>
            <a href="{{ route('cms.pages.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden p-6 md:p-8">
                <form action="{{ route('cms.pages.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-bold text-gray-700">Judul Halaman</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                               placeholder="Contoh: Sejarah Singkat Sekolah">
                        @error('title')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-bold text-gray-700">Slug URL</label>
                        <div class="mt-1 flex rounded-xl shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-xs font-mono">
                                /page/
                            </span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                                   class="flex-1 block w-full rounded-r-xl border-gray-300 focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-mono"
                                   placeholder="sejarah-singkat-sekolah">
                        </div>
                        <p class="mt-1 text-xs text-gray-400">Gunakan huruf kecil, angka, dan tanda hubung saja. URL unik yang akan diakses pengunjung.</p>
                        @error('slug')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-bold text-gray-700">Meta Deskripsi (SEO)</label>
                        <input type="text" name="meta_description" id="meta_description" value="{{ old('meta_description') }}"
                               class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                               placeholder="Deskripsi singkat untuk pencarian Google (maksimal 255 karakter)">
                        @error('meta_description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-bold text-gray-700">Konten Halaman</label>
                        <p class="text-xs text-gray-400 mb-2">Tulis konten lengkap halaman. Anda bisa menulis dalam format HTML atau teks biasa.</p>
                        <textarea name="content" id="content" rows="12" required
                                  class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-medium"
                                  placeholder="Tulis sejarah, visi, misi, atau informasi lainnya di sini...">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                        <div>
                            <label for="order" class="block text-sm font-bold text-gray-700">Urutan Tampilan</label>
                            <input type="number" name="order" id="order" value="{{ old('order', 0) }}" required
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 focus:ring-opacity-50 text-sm font-bold">
                            <p class="mt-1 text-xs text-gray-400">Urutan kemunculan di menu navigasi (angka lebih kecil muncul lebih dulu).</p>
                            @error('order')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col justify-center space-y-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_in_nav" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" {{ old('show_in_nav') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-bold text-gray-700">Tampilkan di Menu Navigasi</span>
                            </label>

                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" {{ old('is_published', '1') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm font-bold text-gray-700">Aktifkan Halaman (Publish)</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-100 flex items-center justify-end space-x-2">
                        <a href="{{ route('cms.pages.index') }}" class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all">
                            Simpan Halaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
        });
    </script>
</x-app-layout>
