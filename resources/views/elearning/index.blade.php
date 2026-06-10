<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
            {{ __('LMS E-Learning Ringan') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
            
            <!-- Welcome Info Box / Install Banner -->
            <div class="bg-gray-900 rounded-[2rem] p-6 sm:p-8 text-white relative overflow-hidden shadow-xl">
                <!-- Decorative Circle -->
                <div class="absolute right-0 bottom-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2">
                        <span class="inline-flex px-3 py-1 bg-emerald-600/30 text-emerald-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                            Portal Akademik Seluler
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black tracking-tight">Selamat Datang di Portal E-Learning</h3>
                        <p class="text-xs sm:text-sm text-gray-300 max-w-xl">
                            Ruang kelas digital yang dioptimalkan untuk perangkat mobile. Baca materi belajar dan selesaikan kuis/ujian mandiri Anda dengan mudah di mana saja.
                        </p>
                    </div>
                    
                    <!-- PWA Installer link if not standalone -->
                    <div id="pwa-badge-container">
                        <a href="{{ route('elearning.install') }}" class="inline-flex px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md items-center space-x-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M8 2a1 1 0 000 2h2a1 1 0 100-2H8z"></path><path d="M3 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6h-4.586l1.293-1.293a1 1 0 00-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L10.414 13H15v3a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"></path></svg>
                            <span>Unduh Aplikasi PWA</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Top Dashboard Actions: Teachers & Admins can Create Class -->
            @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']))
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-4 flex items-center">
                        <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </span>
                        Buat Kelas Virtual Baru
                    </h4>
                    
                    <form action="{{ route('elearning.index') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <div class="space-y-1">
                            <label for="name" class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase">Nama Kelas Virtual</label>
                            <input type="text" name="name" id="name" required placeholder="Contoh: Fisika Kelas X-A" class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs font-semibold px-4 py-3 focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                        </div>
                        <div class="space-y-1">
                            <label for="subject_id" class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase">Mata Pelajaran</label>
                            <select name="subject_id" id="subject_id" required class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs font-semibold px-4 py-3 focus:border-emerald-500">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label for="classroom_id" class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase">Kelas Fisik (Target)</label>
                            <select name="classroom_id" id="classroom_id" required class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs font-semibold px-4 py-3 focus:border-emerald-500">
                                <option value="">-- Pilih Kelas Target --</option>
                                @foreach($classrooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md hover:-translate-y-0.5 transition-all flex items-center justify-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span>Buat Kelas</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Enrolled / Taught Classes Grid -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-xl font-extrabold text-gray-900 dark:text-white flex items-center">
                        <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                        </span>
                        {{ $showArchived ? 'Daftar Arsip Kelas Virtual' : 'Daftar Kelas Virtual Aktif' }}
                    </h3>
                    @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin', 'siswa']))
                        <div class="flex gap-2">
                            @if($showArchived)
                                <a href="{{ route('elearning.index', ['show_archived' => 0]) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                                    Lihat Kelas Aktif
                                </a>
                            @else
                                <a href="{{ route('elearning.index', ['show_archived' => 1]) }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700 flex items-center gap-1.5">
                                    <svg class="w-4.5 h-4.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                    <span>Lihat Arsip Kelas</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if($classes->isEmpty())
                    <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl p-12 text-center max-w-xl mx-auto space-y-4">
                        <svg class="w-16 h-16 text-gray-300 dark:text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h4 class="text-base font-black text-gray-800 dark:text-white">Tidak ada kelas virtual yang aktif</h4>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Hubungi guru pengajar atau admin kurikulum untuk ditambahkan ke dalam kelas virtual materi Anda.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($classes as $class)
                            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between overflow-hidden relative group">
                                <div class="p-6 space-y-4">
                                    <!-- Badges -->
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex px-2 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 rounded text-[10px] font-black uppercase tracking-wider">
                                            {{ $class->subject?->name ?? 'Mapel' }}
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400 dark:text-slate-500">
                                            {{ $class->classroom?->name ?? 'Semua Kelas' }}
                                        </span>
                                    </div>

                                    <!-- Class Info -->
                                    <div class="space-y-1">
                                        <h4 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                            {{ $class->name }}
                                        </h4>
                                        <p class="text-xs text-gray-400 dark:text-slate-500 font-medium">
                                            Pengajar: <span class="font-bold text-gray-600 dark:text-slate-300">{{ $class->teacher?->name ?? 'Guru Pengampu' }}</span>
                                        </p>
                                    </div>
                                    
                                    @if($class->description)
                                        <p class="text-xs text-gray-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                                            {{ $class->description }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Class Footer with Action -->
                                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 flex justify-between items-center">
                                    <div class="flex space-x-3 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            {{ $class->materials->count() }} Materi
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                            {{ $class->assignments->count() }} Tugas/Ujian
                                        </span>
                                    </div>
                                    
                                    <a href="{{ route('elearning.class.show', $class->uuid) }}" class="inline-flex px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all hover:scale-105">
                                        Masuk Kelas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Script to dynamically check standalone PWA and hide download banner -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;
            if (isStandalone) {
                const banner = document.getElementById('pwa-badge-container');
                if (banner) banner.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
