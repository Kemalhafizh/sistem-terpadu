<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-2">
            <div>
                <h2 class="font-extrabold text-3xl text-slate-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Analitik Alur Kerja') }}
                </h2>
                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 tracking-widest uppercase mt-1">
                    SISTA • METRIK SLA & KINERJA PERSETUJUAN
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

        $totalPending = $pendingOnTime + $pendingOverdue;
        $onTimePercentage = $totalPending > 0 ? round(($pendingOnTime / $totalPending) * 100, 1) : 0;
        $overduePercentage = $totalPending > 0 ? round(($pendingOverdue / $totalPending) * 100, 1) : 0;
    @endphp

    <div class="py-8 bg-slate-50 dark:bg-slate-900 min-h-screen text-slate-900 dark:text-slate-100">
        <div class="w-full sm:px-6 lg:px-8 space-y-8 animate-fade-in-up">
            
            <!-- Navigation Bar -->
            <nav class="flex flex-wrap gap-2 p-1.5 bg-white dark:bg-slate-800 rounded-[1.5rem] border border-slate-200/50 dark:border-slate-700/50 shadow-sm">
                <a href="{{ route('workflows.dashboard') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 text-slate-600 dark:text-slate-400 hover:text-slate-950 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-900">
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
                <a href="{{ route('workflows.analytics') }}" class="px-5 py-2.5 text-xs font-bold uppercase tracking-widest rounded-[1.25rem] transition-all duration-300 bg-teal-500 text-white shadow-lg shadow-teal-500/25">
                    Analitik
                </a>
            </nav>

            <!-- SLA Compliance Panel -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-700/50 shadow-xl">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <div>
                        <span class="text-[0.65rem] font-extrabold uppercase tracking-widest text-teal-600 dark:text-teal-400">KPI KEPATUHAN</span>
                        <h3 class="text-xl font-extrabold text-slate-900 dark:text-white mt-1">SLA Compliance Rate</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Rasio kepatuhan waktu pelayanan terhadap batas SLA yang ditentukan.</p>
                    </div>
                    <div class="flex items-center space-x-6 bg-slate-50 dark:bg-slate-900 px-6 py-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                        <div class="text-center">
                            <span class="block text-2xl font-extrabold text-teal-600 dark:text-teal-400">{{ $pendingOnTime }}</span>
                            <span class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">Tepat Waktu</span>
                        </div>
                        <div class="h-8 w-px bg-slate-200 dark:bg-slate-800"></div>
                        <div class="text-center">
                            <span class="block text-2xl font-extrabold text-rose-500">{{ $pendingOverdue }}</span>
                            <span class="text-[0.65rem] font-bold text-slate-400 uppercase tracking-wider">Terlambat</span>
                        </div>
                    </div>
                </div>

                <!-- Visual progress bar comparison -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <span class="text-teal-600 dark:text-teal-400">SLA Met ({{ $onTimePercentage }}%)</span>
                        <span class="text-rose-500">SLA Breached ({{ $overduePercentage }}%)</span>
                    </div>
                    <div class="w-full h-6 bg-slate-100 dark:bg-slate-900 rounded-full overflow-hidden flex border border-slate-200/55 dark:border-slate-800/80 p-1">
                        @if($totalPending > 0)
                            <div class="h-full bg-teal-500 rounded-full transition-all duration-500 shadow-[0_0_8px_rgba(20,184,166,0.3)]" style="width: {{ $onTimePercentage }}%"></div>
                            <div class="h-full bg-rose-500 rounded-full transition-all duration-500 shadow-[0_0_8px_rgba(239,68,68,0.3)] ml-0.5" style="width: {{ $overduePercentage }}%"></div>
                        @else
                            <div class="w-full h-full bg-slate-200 dark:bg-slate-800 rounded-full flex items-center justify-center">
                                <span class="text-[0.65rem] text-slate-400 font-extrabold tracking-widest uppercase">TIDAK ADA ANTREAN PENDING</span>
                            </div>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 text-center font-medium">
                        * Data dihitung dari total antrean persetujuan aktif saat ini yang sedang berjalan.
                    </p>
                </div>
            </div>

            <!-- Two Columns Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Box 1: Rerata Durasi per Tipe Alur -->
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-700/50 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">Rerata Durasi per Tipe Alur</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Waktu rata-rata hingga mencapai persetujuan penuh/penolakan akhir.</p>
                            </div>
                            <span class="p-2.5 bg-teal-500/10 text-teal-600 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                        </div>

                        <div class="space-y-5">
                            @forelse($avgTimes as $time)
                                @php
                                    $avgHours = round($time->avg_hours, 1);
                                    // Determine visual progress bar widths
                                    $maxHoursEst = 120; // 5 days max scale reference
                                    $percentWidth = min(($avgHours / $maxHoursEst) * 100, 100);
                                @endphp
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-sm font-bold">
                                        <span class="text-slate-700 dark:text-slate-300">{{ $time->name }}</span>
                                        <span class="text-teal-600 dark:text-teal-400">{{ $avgHours }} Jam</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-50 dark:bg-slate-900 rounded-full overflow-hidden border border-slate-200/50 dark:border-slate-800/80">
                                        <div class="h-full bg-gradient-to-r from-teal-400 to-teal-500 rounded-full" style="width: {{ $percentWidth ?: 2 }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
                                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <p class="text-sm font-semibold">Belum ada data alur kerja yang terselesaikan untuk dianalisis.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Box 2: Bottleneck Analysis per Peran -->
                <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-700/50 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white leading-tight">Waktu Respon per Jabatan</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Waktu respons rata-rata untuk memberikan persetujuan per peran.</p>
                            </div>
                            <span class="p-2.5 bg-rose-500/10 text-rose-500 rounded-xl">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </span>
                        </div>

                        <div class="space-y-5">
                            @forelse($roleTimes as $rt)
                                @php
                                    $avgRoleHours = round($rt->avg_hours, 1);
                                    $maxRoleHoursEst = 72; // 3 days reference
                                    $rolePercentWidth = min(($avgRoleHours / $maxRoleHoursEst) * 100, 100);
                                    
                                    // Change color based on response speed
                                    $barColor = 'from-teal-400 to-teal-500';
                                    if ($avgRoleHours > 48) {
                                        $barColor = 'from-rose-400 to-rose-500';
                                    } elseif ($avgRoleHours > 24) {
                                        $barColor = 'from-amber-400 to-amber-500';
                                    }
                                @endphp
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-sm font-bold">
                                        <span class="text-slate-700 dark:text-slate-300">
                                            {{ $roleNames[$rt->role] ?? Str::headline($rt->role) }}
                                        </span>
                                        <span class="text-sm font-extrabold @if($avgRoleHours > 48) text-rose-500 @elseif($avgRoleHours > 24) text-amber-500 @else text-teal-600 dark:text-teal-400 @endif">
                                            {{ $avgRoleHours }} Jam
                                        </span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-50 dark:bg-slate-900 rounded-full overflow-hidden border border-slate-200/50 dark:border-slate-800/80">
                                        <div class="h-full bg-gradient-to-r {{ $barColor }} rounded-full" style="width: {{ $rolePercentWidth ?: 2 }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
                                    <svg class="w-12 h-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <p class="text-sm font-semibold">Belum ada keputusan persetujuan yang tercatat untuk dianalisis.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
