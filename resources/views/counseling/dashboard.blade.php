<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                    {{ __('Bimbingan & Konseling (BK Online)') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • LAYANAN KESEJAHTERAAN & KONSELING SISWA</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('counseling.request-form') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Sesi Konseling
                </a>
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Daftar Semua Sesi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ 
        openScheduleModal(sessionId, studentName) {
            $dispatch('open-modal', 'schedule-session-modal');
            this.activeSessionId = sessionId;
            this.activeStudentName = studentName;
        },
        openCancelModal(sessionId, studentName) {
            $dispatch('open-modal', 'cancel-session-modal');
            this.cancelSessionId = sessionId;
            this.cancelStudentName = studentName;
        },
        activeSessionId: null,
        activeStudentName: '',
        cancelSessionId: null,
        cancelStudentName: ''
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/50 flex items-center text-indigo-800 dark:text-indigo-300">
                    <svg class="w-5 h-5 mr-3 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- 4-Grid Statistik Panel -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat 1: Sesi Hari Ini -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-indigo-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Sesi Hari Ini</p>
                        <h3 class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $todaySessionsCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Agenda konseling aktif</p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 2: Antrian Permintaan -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-violet-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Antrean Permintaan</p>
                        <h3 class="text-4xl font-extrabold text-violet-600 dark:text-violet-500">{{ $requestedQueueCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Perlu dijadwalkan</p>
                    </div>
                    <div class="w-14 h-14 bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 3: Sesi Mendatang -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-purple-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Sesi Terjadwal</p>
                        <h3 class="text-4xl font-extrabold text-purple-600 dark:text-purple-500">{{ $upcomingSessionsCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Mendatang dalam agenda</p>
                    </div>
                    <div class="w-14 h-14 bg-purple-50 dark:bg-purple-950/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 4: Perlu Follow Up -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-fuchsia-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Butuh Follow-Up</p>
                        <h3 class="text-4xl font-extrabold text-fuchsia-600 dark:text-fuchsia-500">{{ $needsFollowUpCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Jadwal evaluasi terdekat</p>
                    </div>
                    <div class="w-14 h-14 bg-fuchsia-50 dark:bg-fuchsia-950/30 text-fuchsia-600 dark:text-fuchsia-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Timeline & Agenda Hari Ini (Left 2/3) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Sesi Hari Ini -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                                Sesi Konseling Hari Ini
                            </h4>
                            <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 rounded-full text-xs font-black">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse($todaySessions as $session)
                                <div class="p-5 bg-gradient-to-r from-indigo-50/30 to-purple-50/20 dark:from-indigo-950/10 dark:to-purple-950/5 border border-indigo-100/50 dark:border-indigo-900/30 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 group transition-all hover:border-indigo-500/30">
                                    <div class="flex items-start">
                                        <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-lg mr-4 shadow-sm">
                                            {{ substr($session->student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <a href="{{ route('counseling.student-profile', $session->student) }}" class="font-extrabold text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                                    {{ $session->student->user->name }}
                                                </a>
                                                <span class="text-xs text-gray-400 dark:text-gray-500">({{ $session->student->classroom->name ?? 'Tidak ada kelas' }})</span>
                                                <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300">
                                                    {{ $session->category }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 line-clamp-1">Alasan: <span class="italic">"{{ $session->reason }}"</span></p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Jam: *{{ $session->scheduled_at->format('H:i') }}* • Konselor: {{ $session->counselor->name ?? '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($session->status === 'scheduled')
                                            <form action="{{ route('counseling.start', $session) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                                                    Mulai Sesi
                                                </button>
                                            </form>
                                        @elseif($session->status === 'in_progress')
                                            <a href="{{ route('counseling.session', $session) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10">
                                                Interaksi Live
                                            </a>
                                        @endif
                                        <button @click="openCancelModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="p-2 bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-600 dark:bg-gray-900/50 dark:hover:bg-red-950/50 rounded-xl transition-all border border-gray-100 dark:border-gray-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-900/20 border border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Tidak ada agenda sesi konseling hari ini.</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Gunakan tombol di atas untuk membuat janji temu baru.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Antrian Permintaan Sesi Konseling -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                <span class="w-2.5 h-6 bg-violet-600 rounded-full mr-3"></span>
                                Permohonan Sesi Baru (Menunggu Konfirmasi)
                            </h4>
                            <span class="px-2.5 py-1 bg-violet-50 dark:bg-violet-950/50 text-violet-700 dark:text-violet-400 rounded-full text-xs font-black">
                                Antrean
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse($requestedSessions as $session)
                                <div class="p-5 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 group transition-all hover:border-violet-500/30">
                                    <div class="flex items-start">
                                        <div class="w-11 h-11 bg-violet-100 dark:bg-violet-950/50 text-violet-700 dark:text-violet-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-sm mr-4">
                                            {{ substr($session->student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $session->student->user->name }}</span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500">({{ $session->student->classroom->name ?? '-' }})</span>
                                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded bg-violet-100 dark:bg-violet-950 text-violet-800 dark:text-violet-300">
                                                    {{ $session->category }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Alasan: <span class="italic">"{{ $session->reason }}"</span></p>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">
                                                Diajukan oleh: <span class="text-indigo-600 dark:text-indigo-400">{{ $session->requester->name ?? 'Sistem' }}</span> • {{ $session->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button @click="openScheduleModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md">
                                            Jadwalkan
                                        </button>
                                        <button @click="openCancelModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                                            Tolak
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 bg-gray-50 dark:bg-gray-900/20 border border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Antrean permohonan kosong.</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Semua permintaan siswa telah dikonfirmasi dan dijadwalkan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

                <!-- Right Side Widgets (1/3) -->
                <div class="space-y-8">
                    
                    <!-- Grafik ApexCharts (Kategori Permasalahan) -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6 flex items-center">
                            <span class="w-2 h-4 bg-indigo-500 rounded mr-2"></span>
                            Distribusi Kategori Masalah
                        </h4>
                        
                        <div id="counselingCategoryChart" class="w-full"></div>
                    </div>

                    <!-- Agenda Sesi Mendatang -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                                Sesi Mendatang
                            </h4>
                            <span class="w-2 h-2 bg-purple-500 rounded-full animate-ping"></span>
                        </div>

                        <div class="space-y-4">
                            @forelse($upcomingSessions as $session)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-950/30 border border-gray-100 dark:border-gray-900 rounded-xl">
                                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center shrink-0 font-extrabold text-xs">
                                        {{ $session->scheduled_at->format('d/M') }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-extrabold text-gray-900 dark:text-white truncate">{{ $session->student->user->name }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 truncate mt-0.5">
                                            {{ $session->scheduled_at->format('H:i') }} WIB • {{ ucfirst($session->category) }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 text-center py-4">Belum ada agenda terjadwal berikutnya.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Sesi Membutuhkan Tindak Lanjut -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">
                                Evaluasi Tindak Lanjut
                            </h4>
                        </div>

                        <div class="space-y-4">
                            @forelse($followUpSessions as $session)
                                <div class="flex items-center justify-between p-3 bg-fuchsia-50/30 dark:bg-fuchsia-950/10 border border-fuchsia-100/50 dark:border-fuchsia-900/30 rounded-xl">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-extrabold text-gray-900 dark:text-white truncate">{{ $session->student->user->name }}</p>
                                        <p class="text-[10px] font-bold text-fuchsia-600 dark:text-fuchsia-400 mt-0.5">
                                            Jatuh Tempo: {{ $session->follow_up_date->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('counseling.session', $session) }}" class="p-1.5 bg-fuchsia-100 dark:bg-fuchsia-950/50 text-fuchsia-600 dark:text-fuchsia-400 hover:bg-fuchsia-200 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 text-center py-4">Tidak ada sesi yang memerlukan follow-up dekat ini.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: JADWALKAN & PILIH KONSELOR -->
        <!-- ========================================== -->
        <x-modal name="schedule-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${activeSessionId}/schedule`" class="p-6">
                @csrf
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">
                    Konfirmasi & Penjadwalan Sesi
                </h3>
                <p class="text-xs text-gray-400 mt-1 uppercase font-black tracking-widest">Siswa: <span class="text-indigo-600" x-text="activeStudentName"></span></p>
                
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="counselor_id" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Tentukan Konselor (Guru BK)</label>
                        <select name="counselor_id" id="counselor_id" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Guru BK --</option>
                            @foreach($counselors as $bk)
                                <option value="{{ $bk->id }}">{{ $bk->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="scheduled_at" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Tanggal & Waktu Konseling</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Terapkan Sesi
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- ========================================== -->
        <!-- MODAL 2: BATALKAN SESI -->
        <!-- ========================================== -->
        <x-modal name="cancel-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${cancelSessionId}/cancel`" class="p-6">
                @csrf
                <h3 class="text-lg font-extrabold text-red-600 dark:text-red-500">
                    Batalkan Sesi Bimbingan & Konseling
                </h3>
                <p class="text-xs text-gray-400 mt-1 uppercase font-black tracking-widest">Siswa: <span class="text-red-600" x-text="cancelStudentName"></span></p>
                
                <div class="mt-6">
                    <label for="cancel_reason" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Alasan Pembatalan Sesi</label>
                    <textarea name="cancel_reason" id="cancel_reason" required rows="3" placeholder="Masukkan alasan resmi pembatalan sesi konseling ini..." class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                        Kembali
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Batalkan Sesi
                    </button>
                </div>
            </form>
        </x-modal>

    </div>

    <!-- Scripts Block -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Data Agregat untuk Chart ApexCharts
            const pCategory = parseInt("{{ $categoryStats['pribadi'] }}");
            const pAcademic = parseInt("{{ $categoryStats['akademik'] }}");
            const pSocial = parseInt("{{ $categoryStats['sosial'] }}");
            const pCareer = parseInt("{{ $categoryStats['karir'] }}");

            const options = {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: [pCategory, pAcademic, pSocial, pCareer],
                labels: ['Pribadi', 'Akademik', 'Sosial', 'Karir'],
                colors: ['#6366f1', '#a855f7', '#ec4899', '#f43f5e'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    offsetY: -5
                                },
                                value: {
                                    show: true,
                                    fontSize: '20px',
                                    fontWeight: 800,
                                    offsetY: 5,
                                    formatter: function (val) {
                                        return val + " Sesi"
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Sesi',
                                    fontSize: '12px',
                                    fontWeight: 700,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + " Sesi"
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600,
                    markers: { radius: 12 }
                }
            };

            const chart = new ApexCharts(document.querySelector("#counselingCategoryChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
