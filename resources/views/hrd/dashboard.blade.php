<x-app-layout>
    <x-slot name="title">Dashboard Kepegawaian SISTA</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Dashboard Kepegawaian & HRD
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ringkasan data, pelatihan, evaluasi, dan dokumen kepegawaian {{ school_name() }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hrd.org-chart') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                     Organogram Sekolah
                </a>
                <a href="{{ route('hrd.attendance-report') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                     Rekap Kehadiran
                </a>
                <a href="{{ route('hrd.evaluations.report') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                     Rekap Evaluasi
                </a>
                <a href="{{ route('hrd.certification-tracker') }}" class="inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                     Radar Kadaluarsa
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Stat Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Pegawai -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Pegawai</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $totalEmployees }} <span class="text-xs font-bold text-gray-400 ml-1">orang</span></h3>
                </div>
            </div>

            <!-- Total Guru -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Tenaga Pengajar (Guru)</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $totalGuru }} <span class="text-xs font-bold text-gray-400 ml-1">orang</span></h3>
                </div>
            </div>

            <!-- Total Staf -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Staf / Karyawan</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $totalStaf }} <span class="text-xs font-bold text-gray-400 ml-1">orang</span></h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Status Kepegawaian Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm mb-4"> Distribusi Status Kepegawaian</h3>
                <div class="relative w-full aspect-square min-h-[220px]">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Dokumen Kadaluarsa Alert -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2 space-y-4">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Dokumen & Kontrak Expiring Alert (90 Hari ke Depan)</h3>
                <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($expiringDocuments as $doc)
                        <div class="pt-3 first:pt-0 flex justify-between items-center text-xs">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-white">{{ $doc->employee->user->name }}</span>
                                <span class="text-[10px] text-gray-400 ml-1">({{ $doc->employee->position }})</span>
                                <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold mt-0.5"> Berkas: {{ $doc->title }} (Tipe: {{ strtoupper($doc->type) }})</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 rounded-lg">
                                    Berakhir: {{ $doc->expiry_date->format('d M Y') }}
                                </span>
                                <p class="text-[9px] text-gray-400 mt-1">Sisa {{ now()->diffInDays($doc->expiry_date) }} hari lagi</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-12">Semua dokumen kepegawaian aman dan berstatus aktif.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('statusChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['GTY', 'GTT', 'Staf Tetap', 'Honorer'],
                    datasets: [{
                        data: [
                            {{ $statusDistribution['gty'] }},
                            {{ $statusDistribution['gtt'] }},
                            {{ $statusDistribution['staf_tetap'] }},
                            {{ $statusDistribution['honorer'] }}
                        ],
                        backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
