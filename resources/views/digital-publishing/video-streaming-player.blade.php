<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Studio Pembelajaran Video & Streaming Adaptif') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Platform video interaktif materi guru dengan kualitas streaming otomatis (HLS 360p, 720p, 1080p).
                </p>
            </div>
            <button onclick="document.getElementById('uploadVideoModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span>Unggah Video Guru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Video Showcase Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($videos as $vid)
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-100 dark:border-slate-800 shadow-sm space-y-3 hover:border-emerald-500 transition-all flex flex-col justify-between">
                    <div>
                        <!-- Thumbnail Frame -->
                        <div class="aspect-video bg-slate-900 relative flex items-center justify-center text-white">
                            <div class="w-12 h-12 rounded-full bg-emerald-600/90 flex items-center justify-center shadow-lg cursor-pointer hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                            <span class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-black/70 text-[10px] font-bold">
                                {{ floor($vid->duration_seconds / 60) }}:{{ sprintf('%02d', $vid->duration_seconds % 60) }}
                            </span>
                        </div>

                        <div class="p-5 space-y-1">
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase">HLS Adaptif</span>
                            <h3 class="font-bold text-base text-slate-900 dark:text-white line-clamp-2">{{ $vid->title }}</h3>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ $vid->description ?? 'Materi video pembelajaran mandiri.' }}</p>
                        </div>
                    </div>

                    <div class="px-5 pb-5 pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Pengunggah: {{ $vid->uploader->name ?? 'Guru' }}</span>
                        <span>{{ $vid->view_count }}x ditonton</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Video Pembelajaran</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">Guru dapat mengunggah video materi berdurasi panjang dengan sistem auto-transcoding resolusi ganda.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
