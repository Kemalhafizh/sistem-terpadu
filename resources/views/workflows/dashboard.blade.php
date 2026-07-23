<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-2">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Approval Workflow Dashboard') }}
                </h2>
                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 tracking-widest uppercase mt-1">
                    SISTA • ENGINE ALUR PERSETUJUAN DIGITAL
                </p>
            </div>
        </div>
    </x-slot>

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    @php
        $roleNames = [
            'admin' => 'Administrator',
            'superadmin' => 'Super Administrator',
            'kepala_sekolah' => 'Kepala Sekolah',
            'waka_kurikulum' => 'Waka Kurikulum',
            'waka_kesiswaan' => 'Waka Kesiswaan',
            'waka_sarpras' => 'Waka Sarpras',
            'waka_humas' => 'Waka Humas',
            'staf_tu' => 'Staf Tata Usaha',
            'bk' => 'Bimbingan Konseling (BK)',
            'guru' => 'Guru',
        ];
    @endphp

    <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-screen text-slate-900 dark:text-slate-100">
        <div class="w-full sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <!-- Navigation Bar -->
            <nav class="flex flex-wrap gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-[1.5rem] border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                <a href="{{ route('workflows.dashboard') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 bg-teal-500 text-white shadow-lg shadow-teal-500/25">
                    Dashboard
                </a>
                <a href="{{ route('workflows.pending') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Pending Approvals
                </a>
                <a href="{{ route('workflows.my-requests') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    My Requests
                </a>
                <a href="{{ route('workflows.definitions') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Alur Dinamis
                </a>
                <a href="{{ route('workflows.history') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Riwayat
                </a>
                <a href="{{ route('workflows.analytics') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
                    Analitik
                </a>
            </nav>

            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-teal-500/10 border border-teal-500/30 rounded-[1.5rem] text-teal-600 dark:text-teal-400 text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-[1.5rem] text-rose-600 dark:text-rose-400 text-sm flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- KPI Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                <!-- 1. Total Pending -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400">PENDING</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ $kpi['pending'] }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Total Tertunda</p>
                    </div>
                </div>

                <!-- 2. Disetujui -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400">APPROVED</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-emerald-500">{{ $kpi['approved'] }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Disetujui</p>
                    </div>
                </div>

                <!-- 3. Ditolak -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400">REJECTED</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-rose-500">{{ $kpi['rejected'] }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Ditolak</p>
                    </div>
                </div>

                <!-- 4. Butuh Revisi -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-500/10 text-blue-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400">REVISION</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-blue-500">{{ $kpi['revision'] }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Butuh Revisi</p>
                    </div>
                </div>

                <!-- 5. Tugas Saya -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between border-l-4 border-l-teal-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-teal-500/10 text-teal-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-teal-600 dark:text-teal-400">MY TASK</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-teal-600 dark:text-teal-400">{{ $myPendingCount }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">Tugas Saya</p>
                    </div>
                </div>

                <!-- 6. SLA Breached -->
                <div class="bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700/50 rounded-[2.5rem] p-6 shadow-xl shadow-slate-100/50 dark:shadow-none transition-all duration-300 hover:scale-105 flex flex-col justify-between border-l-4 border-l-rose-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-rose-600">OVERDUE</span>
                    </div>
                    <div>
                        <div class="text-4xl font-extrabold text-rose-600">{{ $overdueCount }}</div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">SLA Breached</p>
                    </div>
                </div>
            </div>

            <!-- Resolution Time Metric Box -->
            <div class="bg-gradient-to-r from-teal-500 to-teal-700 dark:from-slate-800 dark:to-slate-800/80 rounded-[2.5rem] p-8 border border-teal-500/20 dark:border-slate-700 shadow-2xl shadow-teal-500/10 dark:shadow-none flex flex-col md:flex-row items-center justify-between relative overflow-hidden transition-all duration-300 hover:scale-[1.01]">
                <div class="absolute -right-10 -bottom-10 opacity-10 dark:opacity-5 text-white">
                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="z-10 text-center md:text-left mb-6 md:mb-0">
                    <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-teal-200 dark:text-teal-400">PERFORMANCE METRIC</span>
                    <h3 class="text-xl font-extrabold text-white mt-1">Rata-rata Waktu Penyelesaian Alur Kerja</h3>
                    <p class="text-sm text-teal-100 dark:text-slate-400 mt-1 max-w-xl">
                        Diukur berdasarkan durasi antara waktu pengajuan dibuat hingga disetujui penuh atau ditolak di langkah akhir.
                    </p>
                </div>
                <div class="z-10 flex flex-col items-center bg-white/10 dark:bg-slate-900/50 backdrop-blur-md px-8 py-5 rounded-[2rem] border border-white/20 dark:border-slate-700/50">
                    <div class="text-5xl font-extrabold text-white dark:text-teal-400 tracking-tight">{{ $avgResolutionTime }}</div>
                    <div class="text-xs font-bold text-teal-200 dark:text-slate-400 uppercase tracking-widest mt-1">JAM RESOLUSI</div>
                </div>
            </div>

            <!-- Two Columns Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Column 1: Bottlenecks (Daftar Antrean per Peran) -->
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-700/50 shadow-xl shadow-slate-100/50 dark:shadow-none flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">Bottlenecks</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Daftar Antrean Persetujuan per Peran Jabatan</p>
                            </div>
                            <span class="p-2.5 bg-rose-500/10 text-rose-500 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse($bottlenecks as $b)
                                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 transition-all duration-300 hover:scale-[1.01]">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-teal-500"></div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                                            {{ $roleNames[$b->role] ?? Str::headline($b->role) }}
                                        </span>
                                    </div>
                                    <span class="px-3.5 py-1.5 bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 font-extrabold text-xs rounded-full border border-rose-200/50 dark:border-rose-900/30">
                                        {{ $b->count }} Tertunda
                                    </span>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
                                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-semibold">Tidak ada antrean tertunda untuk peran apa pun.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Column 2: SLA Breached List (Overdue Requests) -->
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-700/50 shadow-xl shadow-slate-100/50 dark:shadow-none flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">Daftar Pelanggaran SLA</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pengajuan yang Melebihi Batas Waktu Respon</p>
                            </div>
                            <span class="p-2.5 bg-rose-500/10 text-rose-500 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        </div>

                        <div class="space-y-4">
                            @forelse($overdueRequests as $req)
                                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 flex justify-between items-center transition-all duration-300 hover:scale-[1.01]">
                                    <div class="flex-1 min-w-0 pr-4">
                                        <h4 class="font-extrabold text-slate-800 dark:text-slate-200 text-sm truncate">
                                            {{ $req->definition?->name ?? 'Tanpa Nama Alur' }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                                            <span>Pengaju: <strong class="text-slate-700 dark:text-slate-300">{{ $req->requester?->name ?? 'Anonim' }}</strong></span>
                                            <span>•</span>
                                            <span class="text-rose-500 dark:text-rose-400 font-bold flex items-center">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Jatuh tempo {{ \Carbon\Carbon::parse($req->due_at)->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                    <a href="{{ route('workflows.show', $req->id) }}" class="inline-flex items-center px-4 py-2 bg-teal-500 hover:bg-teal-600 text-white text-xs font-bold rounded-xl transition-all duration-200 shadow-lg shadow-teal-500/10">
                                        Detail
                                    </a>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
                                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-semibold">Luar biasa! Tidak ada pengajuan yang melanggar batas SLA.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
