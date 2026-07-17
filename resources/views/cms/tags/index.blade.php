<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-600">SMA ISLAM SULTAN AGUNG 1 • PENGELOLAAN KONTEN</p>
                <h2 class="font-extrabold text-xl text-gray-800 dark:text-gray-100 leading-tight mt-1">
                    {{ __('Manajemen Tag & Kategori') }}
                </h2>
            </div>
            <a href="{{ route('cms.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Dashboard CMS</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 px-5 py-4 rounded-2xl relative flex items-center space-x-3" role="alert">
                    <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="block sm:inline font-semibold text-sm">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Info Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8 relative overflow-hidden">
                {{-- Decorative gradient accent --}}
                <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-teal-50 to-transparent dark:from-teal-900/20 rounded-bl-full pointer-events-none"></div>

                <div class="relative flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg shadow-teal-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 mb-1">PANDUAN TAG</p>
                        <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Kelola Tag Konten</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            Tag digunakan untuk mengkategorikan dan mengelompokkan konten berita, artikel, dan halaman di website sekolah.
                            Tulis semua tag dalam satu kolom, <span class="font-bold text-gray-700 dark:text-gray-300">pisahkan dengan koma</span> untuk setiap tag baru.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tag Management Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-teal-50 dark:bg-teal-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100">Editor Tag</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-0.5">INPUT & KONFIGURASI</p>
                    </div>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('cms.tags.index') }}" method="POST" class="p-8 space-y-6">
                    @csrf

                    <div class="space-y-3">
                        <label for="tags" class="block">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Daftar Tag</span>
                        </label>
                        <textarea
                            id="tags"
                            name="tags"
                            rows="6"
                            class="block w-full rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 dark:focus:ring-teal-800 focus:ring-opacity-50 text-sm font-medium transition-all placeholder:text-gray-300 dark:placeholder:text-gray-600"
                            placeholder="Berita, Pengumuman, Prestasi, Kegiatan, Akademik..."
                        >{{ $tags }}</textarea>
                        <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Pisahkan setiap tag dengan koma. Contoh: Berita, Pengumuman, Prestasi</span>
                        </p>
                        @error('tags')
                            <p class="text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tag Preview --}}
                    @if(!empty($tags))
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">PREVIEW TAG AKTIF</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach(explode(',', $tags) as $tag)
                                    @if(trim($tag))
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-teal-50 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-100 dark:border-teal-800">
                                            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                            {{ trim($tag) }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Submit --}}
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-700 hover:to-teal-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all transform hover:-translate-y-0.5 space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Simpan Tag</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
