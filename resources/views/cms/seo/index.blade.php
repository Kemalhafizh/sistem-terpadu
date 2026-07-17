<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-600">SMA ISLAM SULTAN AGUNG 1 • OPTIMASI MESIN PENCARI</p>
                <h2 class="font-extrabold text-xl text-gray-800 dark:text-gray-100 leading-tight mt-1">
                    {{ __('Pengaturan SEO Global') }}
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

            {{-- SEO Info Banner --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 p-8 relative overflow-hidden">
                {{-- Decorative gradient --}}
                <div class="absolute top-0 right-0 w-48 h-48 bg-gradient-to-bl from-teal-50 to-transparent dark:from-teal-900/20 rounded-bl-full pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-blue-50 to-transparent dark:from-blue-900/10 rounded-tr-full pointer-events-none"></div>

                <div class="relative flex items-start space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-teal-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-teal-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 mb-1">SEARCH ENGINE OPTIMIZATION</p>
                        <h3 class="text-lg font-extrabold text-gray-900 dark:text-gray-100">Konfigurasi SEO Website</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                            Atur meta tag default untuk seluruh halaman website sekolah. Pengaturan ini mempengaruhi bagaimana
                            website tampil di hasil pencarian Google dan saat dibagikan di media sosial.
                        </p>
                    </div>
                </div>
            </div>

            {{-- SEO Settings Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                {{-- Card Header --}}
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-teal-50 dark:bg-teal-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100">Pengaturan Meta Tag</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-0.5">KONFIGURASI GLOBAL</p>
                    </div>
                </div>

                {{-- Form Body --}}
                <form action="{{ route('cms.seo.index') }}" method="POST" class="p-8 space-y-8">
                    @csrf

                    {{-- Section: Basic SEO --}}
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="w-1.5 h-5 bg-teal-500 rounded-full"></div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">PENGATURAN DASAR</p>
                        </div>

                        {{-- SEO Title Template --}}
                        <div class="space-y-2">
                            <label for="seo_title_template" class="block">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Template Judul Halaman</span>
                            </label>
                            <input
                                type="text"
                                id="seo_title_template"
                                name="seo_title_template"
                                value="{{ $seo['title_template'] }}"
                                placeholder="SISTA | {title}"
                                class="block w-full rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 dark:focus:ring-teal-800 focus:ring-opacity-50 text-sm font-medium transition-all placeholder:text-gray-300 dark:placeholder:text-gray-600"
                            >
                            <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Gunakan <code class="text-[10px] bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded font-mono">{title}</code> sebagai placeholder untuk judul halaman dinamis</span>
                            </p>
                            @error('seo_title_template')
                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Meta Description --}}
                        <div class="space-y-2">
                            <label for="seo_meta_description" class="block">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Meta Deskripsi Default</span>
                            </label>
                            <textarea
                                id="seo_meta_description"
                                name="seo_meta_description"
                                rows="4"
                                class="block w-full rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 dark:focus:ring-teal-800 focus:ring-opacity-50 text-sm font-medium transition-all placeholder:text-gray-300 dark:placeholder:text-gray-600"
                                placeholder="Deskripsi singkat tentang website sekolah untuk hasil pencarian..."
                            >{{ $seo['meta_description'] }}</textarea>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center space-x-1.5">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Ideal antara 150–160 karakter untuk tampilan optimal di Google</span>
                                </p>
                                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500" id="desc-counter"></span>
                            </div>
                            @error('seo_meta_description')
                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Section: Open Graph --}}
                    <div class="space-y-6">
                        <div class="flex items-center space-x-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                            <div class="w-1.5 h-5 bg-blue-500 rounded-full"></div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">OPEN GRAPH / MEDIA SOSIAL</p>
                        </div>

                        {{-- OG Title --}}
                        <div class="space-y-2">
                            <label for="seo_og_title" class="block">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Open Graph Title</span>
                            </label>
                            <input
                                type="text"
                                id="seo_og_title"
                                name="seo_og_title"
                                value="{{ $seo['og_title'] }}"
                                placeholder="SISTA — SMA Islam Sultan Agung 1 Semarang"
                                class="block w-full rounded-2xl border-gray-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 dark:focus:ring-teal-800 focus:ring-opacity-50 text-sm font-medium transition-all placeholder:text-gray-300 dark:placeholder:text-gray-600"
                            >
                            <p class="text-xs text-gray-400 dark:text-gray-500 flex items-center space-x-1.5">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Judul yang tampil saat link dibagikan di Facebook, WhatsApp, dan Twitter</span>
                            </p>
                            @error('seo_og_title')
                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- OG Preview --}}
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3">PREVIEW TAMPILAN DI SOSIAL MEDIA</p>
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-600 overflow-hidden max-w-md shadow-sm">
                                {{-- Preview Image Area --}}
                                <div class="h-32 bg-gradient-to-br from-teal-400 via-teal-500 to-blue-600 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="w-10 h-10 mx-auto bg-white/20 rounded-xl backdrop-blur-sm flex items-center justify-center mb-2">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        </div>
                                        <span class="text-xs text-white/80 font-bold">SISTA</span>
                                    </div>
                                </div>
                                {{-- Preview Text --}}
                                <div class="p-3">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200" id="og-preview-title">{{ $seo['og_title'] ?: 'Open Graph Title' }}</p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2" id="og-preview-desc">{{ $seo['meta_description'] ?: 'Meta deskripsi akan tampil di sini...' }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 font-mono">sista.sch.id</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            Perubahan akan berlaku di seluruh halaman publik website.
                        </p>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-700 hover:to-teal-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all transform hover:-translate-y-0.5 space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Simpan Pengaturan SEO</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <script>
        // Character counter for meta description
        const descTextarea = document.getElementById('seo_meta_description');
        const descCounter = document.getElementById('desc-counter');

        function updateCounter() {
            const len = descTextarea.value.length;
            descCounter.textContent = len + ' / 160';
            if (len > 160) {
                descCounter.classList.add('text-red-500');
                descCounter.classList.remove('text-gray-400');
            } else {
                descCounter.classList.remove('text-red-500');
                descCounter.classList.add('text-gray-400');
            }
        }

        descTextarea.addEventListener('input', updateCounter);
        updateCounter();

        // Live OG preview
        const ogTitleInput = document.getElementById('seo_og_title');
        const ogPreviewTitle = document.getElementById('og-preview-title');
        const ogPreviewDesc = document.getElementById('og-preview-desc');

        ogTitleInput.addEventListener('input', function() {
            ogPreviewTitle.textContent = this.value || 'Open Graph Title';
        });

        descTextarea.addEventListener('input', function() {
            ogPreviewDesc.textContent = this.value || 'Meta deskripsi akan tampil di sini...';
        });
    </script>
</x-app-layout>
