<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                    {{ __('Katalog Ekstrakurikuler') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • PENGEMBANGAN DIRI & NON-AKADEMIK</p>
            </div>
            
            @if(in_array(auth()->user()->role, ['guru', 'bk', 'waka_kesiswaan', 'admin', 'superadmin']))
            <div class="flex gap-2">
                <a href="{{ route('ekskul.manage') }}" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Cockpit Pembina
                </a>
            </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            @if(auth()->user()->role === 'student')
                <!-- Student Dashboard Summary Card -->
                <div class="bg-gradient-to-r from-emerald-600/90 to-teal-700/90 dark:from-emerald-950/80 dark:to-teal-950/80 border border-emerald-500/20 backdrop-blur-md rounded-[2.5rem] p-6 md:p-8 text-white shadow-xl relative overflow-hidden group">
                    <div class="relative z-10 space-y-4">
                        <div>
                            <span class="px-3 py-1 bg-white/20 dark:bg-emerald-900/40 rounded-full text-[10px] font-black uppercase tracking-wider">Status Keanggotaan</span>
                            <h3 class="text-2xl font-black mt-2 tracking-tight">Halo, {{ auth()->user()->name }}!</h3>
                            <p class="text-sm text-emerald-100/90 mt-1">Gali minat bakatmu dengan mendaftar di program ekstrakurikuler resmi sekolah. Batas pendaftaran aktif maksimal 2 ekskul.</p>
                        </div>
                        <div class="flex flex-wrap gap-4 pt-2">
                            <div class="bg-white/10 dark:bg-white/5 backdrop-blur-sm px-5 py-3 rounded-2xl border border-white/10">
                                <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-200">Ekskul Terdaftar</p>
                                <p class="text-2xl font-black mt-1">{{ count($myEkskulIds) }} / 2</p>
                            </div>
                            <div class="bg-white/10 dark:bg-white/5 backdrop-blur-sm px-5 py-3 rounded-2xl border border-white/10">
                                <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-200">Kuota Tersisa</p>
                                <p class="text-2xl font-black mt-1">{{ max(0, 2 - count($myEkskulIds)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Grid Kartu Ekskul -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($extracurriculars as $ekskul)
                    @php
                        $slots = $ekskul->available_slots;
                        $isFull = !is_null($ekskul->max_members) && $slots <= 0;
                        $isRegistered = isset($myEkskulIds) && in_array($ekskul->id, $myEkskulIds);
                    @endphp
                    <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2.5rem] overflow-hidden shadow-lg border border-gray-100 dark:border-gray-700/50 hover:border-emerald-500/40 hover:-translate-y-2 transition-all duration-300 flex flex-col group">
                        <!-- Cover Image -->
                        <div class="h-48 w-full bg-slate-100 dark:bg-gray-900 relative overflow-hidden">
                            @if($ekskul->cover_image)
                                <img src="{{ asset('storage/' . $ekskul->cover_image) }}" alt="{{ $ekskul->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-emerald-500/10 to-slate-500/10 flex items-center justify-center relative">
                                    <svg class="w-16 h-16 text-emerald-600/30 dark:text-emerald-400/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Badges floating on image -->
                            <div class="absolute top-4 left-4 flex flex-wrap gap-2">
                                <span class="px-3 py-1 bg-white/95 dark:bg-gray-900/90 text-slate-800 dark:text-white rounded-full text-[10px] font-black uppercase tracking-wider shadow-sm flex items-center">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full mr-1.5 animate-pulse"></span>
                                    {{ $ekskul->schedule_day }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-6">
                            <div class="space-y-3">
                                <div class="flex justify-between items-start gap-2">
                                    <h4 class="text-xl font-extrabold text-gray-900 dark:text-white leading-tight tracking-tight group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ $ekskul->name }}
                                    </h4>
                                    <!-- Slot badge -->
                                    @if(is_null($ekskul->max_members))
                                        <span class="px-2.5 py-1 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-[9px] font-extrabold uppercase tracking-wider shrink-0">Siswa Bebas</span>
                                    @elseif($isFull)
                                        <span class="px-2.5 py-1 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/30 text-red-600 dark:text-red-400 rounded-lg text-[9px] font-extrabold uppercase tracking-wider shrink-0">Penuh</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg text-[9px] font-extrabold uppercase tracking-wider shrink-0">Sisa {{ $slots }} Slot</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                    {{ $ekskul->description ?? 'Tidak ada deskripsi tambahan mengenai kegiatan ekstrakurikuler ini.' }}
                                </p>
                            </div>

                            <!-- Meta Details List -->
                            <div class="space-y-2 border-t border-b border-gray-100 dark:border-gray-700/50 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Pembina: <strong class="text-gray-800 dark:text-gray-200 font-extrabold">{{ $ekskul->advisor->name }}</strong></span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Waktu: <span class="text-gray-700 dark:text-gray-300">{{ $ekskul->schedule_time }} WIB</span></span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>Lokasi: <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $ekskul->location }}</span></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-3">
                                <a href="{{ route('ekskul.show', $ekskul->id) }}" class="flex-grow inline-flex items-center justify-center px-4 py-2.5 bg-slate-50 dark:bg-gray-700 border border-slate-200 dark:border-gray-600 hover:bg-slate-100 dark:hover:bg-gray-600 text-slate-800 dark:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                    Detail Ekskul
                                </a>

                                @if(auth()->user()->role === 'student')
                                    @if($isRegistered)
                                        <button disabled class="flex-grow inline-flex items-center justify-center px-4 py-2.5 bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-black uppercase tracking-wider cursor-not-allowed">
                                            Terdaftar
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('ekskul.register', $ekskul->id) }}" class="flex-grow flex">
                                            @csrf
                                            <button type="submit" 
                                                {{ ($isFull || count($myEkskulIds) >= 2) ? 'disabled' : '' }}
                                                class="w-full inline-flex items-center justify-center px-4 py-2.5 {{ ($isFull || count($myEkskulIds) >= 2) ? 'bg-slate-300 dark:bg-slate-800 text-slate-400 dark:text-slate-600 border border-transparent cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 border border-emerald-500/20' }} rounded-xl text-xs font-black uppercase tracking-wider transition-all active:scale-95">
                                                Daftar
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-[2rem] p-12 text-center border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-slate-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">Belum Ada Ekstrakurikuler Aktif</h4>
                        <p class="text-sm font-semibold text-gray-400 dark:text-gray-500 mt-1">Hubungi Waka Kesiswaan atau Admin untuk mendaftarkan ekskul baru.</p>
                    </div>
                @endforelse
            </div>
            
        </div>
    </div>
</x-app-layout>
