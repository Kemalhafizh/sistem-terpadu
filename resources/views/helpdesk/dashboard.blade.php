<x-app-layout>
    <x-slot name="title">Dashboard Analitik Helpdesk</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight flex items-center gap-2">
                     {{ __('Dashboard Analitik Helpdesk') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Pemantauan KPI performa penyelesaian pengaduan, waktu respons SLA, dan distribusi laporan fasilitas sekolah.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('helpdesk.tickets.index') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-200">
                    Daftar Semua Tiket
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- Total Laporan -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Tiket</p>
                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $total }}</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>

            <!-- Tiket Open -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Status Open</p>
                    <h3 class="text-xl font-black text-blue-600 dark:text-blue-400">{{ $open }}</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">In Progress</p>
                    <h3 class="text-xl font-black text-purple-600 dark:text-purple-400">{{ $inProgress }}</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                </div>
            </div>

            <!-- Resolved & Closed -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Selesai (Resolved)</p>
                    <h3 class="text-xl font-black text-emerald-600 dark:text-emerald-400">{{ $resolved + $closed }}</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- SLA Overdue -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">SLA Overdue</p>
                    <h3 class="text-xl font-black text-rose-600 dark:text-rose-400">{{ $overdue }}</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5 {{ $overdue > 0 ? 'animate-bounce text-rose-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

            <!-- Avg Resolution Time -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-xs flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Rata-rata SLA</p>
                    <h3 class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $avgResolutionHours }} Jam</h3>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Analytical Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Category Distribution Donut Chart -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
                <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider mb-4">Distribusi Laporan per Kategori</h4>
                
                <div class="w-full flex items-center justify-center min-h-[300px]" id="categoriesChartContainer">
                    <div id="categoriesChart" class="w-full"></div>
                </div>
            </div>

            <!-- Resolution Status Bar Chart -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
                <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider mb-4">Statistik Volume Laporan Pengaduan</h4>
                
                <div class="w-full min-h-[300px]" id="statusChartContainer">
                    <div id="statusChart" class="w-full"></div>
                </div>
            </div>

        </div>

        <!-- Critical & High Tickets Table -->
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/80 bg-slate-50/40 dark:bg-slate-950/20 flex items-center justify-between">
                <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                    Pengaduan Kritis Aktif (Prioritas High / Critical)
                </h4>
                <span class="text-[10px] text-slate-400 font-semibold italic">Terbatas hanya menampilkan 5 tiket terbaru</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4">No. Tiket</th>
                            <th class="px-6 py-4">Judul Masalah</th>
                            <th class="px-6 py-4 text-center">Kategori</th>
                            <th class="px-6 py-4 text-center">Prioritas</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Pelapor</th>
                            <th class="px-6 py-4">Batas SLA</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                        @forelse($criticalTickets as $ct)
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-950/10 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-extrabold text-slate-900 dark:text-white uppercase">#{{ $ct->ticket_number }}</span>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="font-bold text-slate-900 dark:text-white truncate" title="{{ $ct->title }}">{{ $ct->title }}</div>
                                    <div class="text-[10px] text-slate-400 truncate mt-0.5" title="{{ $ct->description }}">{{ Str::limit($ct->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $catLabel = match ($ct->category) {
                                            'it' => 'IT Support',
                                            'sarpras' => 'Sarpras',
                                            'akademik' => 'Akademik',
                                            'keuangan' => 'Keuangan',
                                            'umum' => 'Umum',
                                            default => $ct->category,
                                        };
                                        $catColors = match ($ct->category) {
                                            'it' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
                                            'sarpras' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'akademik' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20',
                                            'keuangan' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'umum' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                                            default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $catColors }}">{{ $catLabel }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $priColors = match ($ct->priority) {
                                            'high' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
                                            'critical' => 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30 animate-pulse',
                                            default => 'bg-slate-500/10 border-slate-500/20 text-slate-500',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $priColors }}">{{ strtoupper($ct->priority) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $statColors = match ($ct->status) {
                                            'open' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            'in_progress' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                            'waiting' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            default => 'bg-slate-500/10 border-slate-500/20 text-slate-500',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statColors }}">{{ strtoupper($ct->status) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $ct->reporter?->name }}</div>
                                    <span class="text-[10px] text-slate-400 capitalize">{{ $ct->reporter?->role }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ct->isOverdue())
                                        <span class="px-1.5 py-0.5 bg-red-500/10 border border-red-500/20 text-red-500 text-[9px] font-black uppercase tracking-widest rounded-md animate-pulse">SLA Overdue</span>
                                    @else
                                        <div class="font-bold text-slate-800 dark:text-slate-200">{{ $ct->due_date?->format('d M H:i') }}</div>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">{{ $ct->due_date?->diffForHumans() }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('helpdesk.tickets.show', $ct->uuid) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-600 dark:bg-slate-800 dark:hover:bg-indigo-600 text-slate-700 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-slate-200 dark:border-slate-700/60 hover:border-indigo-600">
                                        Tangani
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 italic">
                                    Tidak ada tiket pengaduan berkategori kritis/tinggi yang aktif saat ini. Kerja bagus!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Script for ApexCharts Rendering -->
    @push('scripts')
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            
            // --- 1. CATEGORY DONUT CHART ---
            const categoriesData = {
                it: {{ $categories['it'] ?? 0 }},
                sarpras: {{ $categories['sarpras'] ?? 0 }},
                akademik: {{ $categories['akademik'] ?? 0 }},
                keuangan: {{ $categories['keuangan'] ?? 0 }},
                umum: {{ $categories['umum'] ?? 0 }},
            };

            const donutOptions = {
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'Inter, system-ui, sans-serif',
                },
                series: [categoriesData.it, categoriesData.sarpras, categoriesData.akademik, categoriesData.keuangan, categoriesData.umum],
                labels: ['IT Support', 'Sarana Prasarana', 'Akademik', 'Keuangan', 'Umum'],
                colors: ['#06b6d4', '#f59e0b', '#8b5cf6', '#10b981', '#64748b'],
                stroke: {
                    show: true,
                    width: 2,
                    colors: [document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff']
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '11px',
                                    fontWeight: 900,
                                    color: '#94a3b8',
                                    offsetY: -6
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 900,
                                    color: document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a',
                                    offsetY: 6,
                                    formatter: function (val) {
                                        return val;
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'TOTAL ADUAN',
                                    fontSize: '9px',
                                    fontWeight: 900,
                                    color: '#64748b',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 700,
                    labels: {
                        colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#475569',
                    },
                    markers: {
                        radius: 6,
                    }
                },
                dataLabels: {
                    enabled: false
                }
            };

            const categoryChart = new ApexCharts(document.querySelector("#categoriesChart"), donutOptions);
            categoryChart.render();


            // --- 2. STATUS SUMMARY BAR CHART ---
            const barOptions = {
                chart: {
                    type: 'bar',
                    height: 320,
                    fontFamily: 'Inter, system-ui, sans-serif',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '45%',
                        borderRadius: 8,
                        dataLabels: {
                            position: 'top'
                        }
                    },
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '10px',
                        fontWeight: 900,
                        colors: [document.documentElement.classList.contains('dark') ? '#ffffff' : '#0f172a']
                    }
                },
                series: [{
                    name: 'Jumlah Tiket',
                    data: [{{ $open }}, {{ $inProgress }}, {{ $resolved }}, {{ $closed }}, {{ $overdue }}]
                }],
                colors: ['#3b82f6', '#a855f7', '#10b981', '#64748b', '#ef4444'],
                xaxis: {
                    categories: ['Open', 'In Progress', 'Resolved', 'Closed', 'Overdue'],
                    labels: {
                        style: {
                            colors: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#475569',
                            fontSize: '10px',
                            fontWeight: 800
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '10px'
                        }
                    }
                },
                grid: {
                    borderColor: document.documentElement.classList.contains('dark') ? '#334155' : '#f1f5f9',
                    strokeDashArray: 4,
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                }
            };

            const statusChart = new ApexCharts(document.querySelector("#statusChart"), barOptions);
            statusChart.render();

        });
    </script>
    @endpush
</x-app-layout>
