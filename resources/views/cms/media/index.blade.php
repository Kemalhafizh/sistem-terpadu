<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-600">SMA ISLAM SULTAN AGUNG 1 • ASET DIGITAL</p>
                <h2 class="font-extrabold text-xl text-gray-800 dark:text-gray-100 leading-tight mt-1">
                    {{ __('Perpustakaan Media') }}
                </h2>
            </div>
            <a href="{{ route('cms.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl transition-all flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Dashboard CMS</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 px-5 py-4 rounded-2xl relative flex items-center space-x-3" role="alert">
                    <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 dark:bg-emerald-800 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="block sm:inline font-semibold text-sm">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Upload Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-teal-50 dark:bg-teal-900/40 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100">Upload File Baru</h3>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-0.5">UNGGAH MEDIA</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700 px-3 py-1.5 rounded-full">
                        {{ count($files) }} file tersimpan
                    </span>
                </div>

                <form action="{{ route('cms.media.index') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                        {{-- File Input Area --}}
                        <div class="flex-1 w-full">
                            <label for="file" class="block mb-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Pilih File</span>
                            </label>
                            <label class="w-full flex items-center justify-center px-6 py-6 bg-gray-50 dark:bg-gray-900 text-gray-400 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 hover:border-teal-400 dark:hover:border-teal-500 hover:text-teal-500 hover:bg-teal-50/50 dark:hover:bg-teal-900/20 transition-all cursor-pointer group">
                                <div class="flex flex-col items-center space-y-2">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-teal-100 dark:group-hover:bg-teal-900/50 flex items-center justify-center transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-sm font-semibold" id="media-file-label">Klik untuk memilih file</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">JPG, PNG, GIF, WEBP, SVG, PDF — Maks 5MB</span>
                                </div>
                                <input type="file" name="file" id="file" class="hidden" accept="image/*,.pdf,.svg" required onchange="updateMediaLabel(this)">
                            </label>
                            @error('file')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Upload Button --}}
                        <button type="submit" class="inline-flex items-center px-6 py-3.5 bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-700 hover:to-teal-600 text-white text-sm font-bold rounded-2xl shadow-lg shadow-teal-500/25 hover:shadow-teal-500/40 transition-all transform hover:-translate-y-0.5 space-x-2 flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            <span>Upload File</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Media Grid --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 flex items-center space-x-3">
                    <div class="w-8 h-8 bg-teal-50 dark:bg-teal-900/40 rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-gray-800 dark:text-gray-100">Koleksi Media</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-0.5">GALERI FILE TERUNGGAH</p>
                    </div>
                </div>

                <div class="p-8">
                    @if(count($files) > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                            @foreach($files as $file)
                                <div class="group relative bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:border-teal-200 dark:hover:border-teal-700 transition-all hover:-translate-y-1">
                                    {{-- File Preview --}}
                                    <div class="aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800 relative">
                                        @php
                                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
                                        @endphp

                                        @if($isImage)
                                            <img src="{{ asset('storage/'.$file) }}" class="w-full h-32 object-cover rounded-xl" alt="{{ basename($file) }}" loading="lazy">
                                        @else
                                            <div class="w-full h-32 flex flex-col items-center justify-center rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
                                                <div class="w-10 h-10 bg-white dark:bg-gray-700 rounded-xl shadow-sm flex items-center justify-center mb-2">
                                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">.{{ $ext }}</span>
                                            </div>
                                        @endif

                                        {{-- Hover Overlay --}}
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-3">
                                            <a href="{{ route('cms.media.index', ['delete' => $file]) }}"
                                               onclick="return confirm('Hapus file ini?')"
                                               class="inline-flex items-center px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-xl shadow-lg transition-all transform translate-y-2 group-hover:translate-y-0 space-x-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                <span>Hapus</span>
                                            </a>
                                        </div>
                                    </div>

                                    {{-- File Info --}}
                                    <div class="p-3">
                                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate" title="{{ basename($file) }}">{{ basename($file) }}</p>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-1">.{{ $ext }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-16">
                            <div class="w-20 h-20 mx-auto bg-gray-50 dark:bg-gray-900 rounded-3xl flex items-center justify-center mb-5">
                                <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h4 class="text-base font-extrabold text-gray-400 dark:text-gray-500">Belum Ada Media</h4>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2 max-w-sm mx-auto">
                                Perpustakaan media masih kosong. Upload file pertama Anda menggunakan form di atas.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        function updateMediaLabel(input) {
            const label = document.getElementById('media-file-label');
            if (input.files.length > 0) {
                label.innerText = input.files[0].name;
                label.classList.add('text-teal-600');
            } else {
                label.innerText = 'Klik untuk memilih file';
                label.classList.remove('text-teal-600');
            }
        }
    </script>
</x-app-layout>
