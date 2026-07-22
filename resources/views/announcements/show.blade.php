<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                <span class="w-8 h-8 bg-primary-100 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 rounded-lg flex items-center justify-center mr-3">
                    
                </span>
                {{ __('Detail Pengumuman') }}
            </h2>
            <a href="{{ url()->previous() == route('announcements.index') ? route('announcements.index') : route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-5 py-4 rounded-2xl relative shadow-sm" role="alert">
                    <span class="block sm:inline font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-md border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Top Header banner -->
                <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-750 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Category -->
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ 
                            $announcement->category === 'akademik' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : (
                            $announcement->category === 'keuangan' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : (
                            $announcement->category === 'kegiatan' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300' : (
                            $announcement->category === 'keislaman' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')))
                        }}">
                             {{ $announcement->category ?? 'umum' }}
                        </span>
                        
                        <!-- Priority -->
                        <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{
                            $announcement->priority === 'urgent' ? 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 animate-pulse' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                        }}">
                             {{ $announcement->priority }}
                        </span>

                        @if($announcement->is_pinned)
                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200/20">
                                 Pinned
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-xs text-gray-400 dark:text-gray-500 font-bold">
                        Dibuat {{ $announcement->created_at->diffForHumans() }} ({{ $announcement->created_at->translatedFormat('d M Y H:i') }})
                    </div>
                </div>

                <div class="p-8 sm:p-10 space-y-8">
                    <!-- Title -->
                    <div>
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                            {{ $announcement->title }}
                        </h1>
                        <div class="mt-3 flex items-center text-sm font-bold text-gray-500 dark:text-gray-450">
                            <span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-750 flex items-center justify-center mr-2 text-base"></span>
                            {{ __('Diterbitkan oleh') }} <span class="text-gray-800 dark:text-gray-250 ml-1">{{ $announcement->creator->name }}</span>
                        </div>
                    </div>

                    <!-- Poster Image -->
                    @if($announcement->image_path)
                        <div class="rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm max-h-[500px] flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                            <img src="{{ Storage::url($announcement->image_path) }}" alt="{{ $announcement->title }}" class="max-w-full max-h-[500px] object-contain">
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="prose max-w-none dark:prose-invert text-base text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $announcement->content }}
                    </div>

                    <!-- File Attachment -->
                    @if($announcement->attachment_path)
                        <div class="p-5 bg-indigo-50/50 dark:bg-indigo-950/10 border border-indigo-100 dark:border-indigo-900/30 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl">
                                    
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Dokumen Lampiran Pengumuman</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-450 mt-0.5">Silakan unduh berkas pendukung di bawah ini.</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($announcement->attachment_path) }}" target="_blank" download class="inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                                Unduh Lampiran
                            </a>
                        </div>
                    @endif

                    <!-- Acknowledgment Feature -->
                    @if($announcement->require_acknowledgement)
                        @php
                            $readRecord = $announcement->reads()->where('user_id', auth()->id())->first();
                            $acknowledgedAt = $readRecord?->acknowledged_at;
                        @endphp

                        <div class="mt-8 pt-8 border-t border-gray-100 dark:border-gray-700">
                            @if($acknowledgedAt)
                                <div class="p-5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/30 rounded-2xl flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                                        
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Konfirmasi Diterima</h4>
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-0.5">Anda telah mengonfirmasi pembacaan pengumuman ini pada {{ $acknowledgedAt->translatedFormat('d F Y H:i') }}.</p>
                                    </div>
                                </div>
                            @else
                                <div class="p-6 bg-rose-50/50 dark:bg-rose-950/10 border border-rose-200/50 dark:border-rose-900/30 rounded-2xl space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                                            
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-black text-rose-900 dark:text-rose-300">Konfirmasi Pembacaan Wajib</h4>
                                            <p class="text-xs text-rose-700 dark:text-rose-400 mt-1 leading-relaxed">
                                                Pengirim meminta konfirmasi resmi bahwa Anda telah membaca dan memahami informasi dalam pengumuman ini. Silakan klik tombol di bawah untuk memberikan tanda konfirmasi.
                                            </p>
                                        </div>
                                    </div>
                                    <form action="{{ route('announcements.acknowledge', $announcement) }}" method="POST" class="pt-2">
                                        @csrf
                                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-md shadow-rose-600/10">
                                            Saya Telah Membaca & Memahami Pengumuman Ini
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
