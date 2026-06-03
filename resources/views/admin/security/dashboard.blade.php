<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-shield-halved text-indigo-400 mr-2"></i> {{ __('Dashboard Keamanan & SIEM') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.security.sessions') }}" id="btn-active-sessions" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg shadow-indigo-500/20 flex items-center">
                    <i class="fa-solid fa-desktop mr-2"></i> Monitor Sesi Aktif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm flex items-center">
                    <i class="fa-solid fa-circle-check mr-2 text-lg"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-400 text-sm flex items-center">
                    <i class="fa-solid fa-triangle-exclamation mr-2 text-lg"></i>
                    {{ session('error') }}
                </div>
            @endif

            <!-- 🚨 BRUTE-FORCE DETECTED ALERTS -->
            @if(!empty($stats['brute_force_alerts']))
                <div id="brute-force-alert-panel" class="bg-rose-950/40 backdrop-blur-xl border border-rose-500/50 rounded-2xl p-6 shadow-2xl shadow-rose-950/20 animate-pulse">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-rose-500/20 p-3 rounded-xl border border-rose-500/30">
                            <i class="fa-solid fa-radiation text-rose-400 text-2xl"></i>
                        </div>
                        <div class="ml-5 flex-1">
                            <h3 class="text-lg font-semibold text-rose-200">Deteksi Serangan Brute-Force Aktif!</h3>
                            <p class="text-sm text-rose-300 mt-1">Sistem mendeteksi aktivitas mencurigakan berupa kegagalan login berulang (&ge; 5 kali) dalam 10 menit terakhir dari alamat IP berikut:</p>
                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($stats['brute_force_alerts'] as $alert)
                                    <div class="bg-rose-900/30 border border-rose-500/30 rounded-xl p-3 flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <i class="fa-solid fa-mask text-rose-400"></i>
                                            <span class="font-mono text-white text-sm font-semibold">{{ $alert['ip_address'] }}</span>
                                        </div>
                                        <span class="bg-rose-500/25 text-rose-200 text-xs px-2.5 py-1 rounded-full font-bold border border-rose-400/30">
                                            {{ $alert['total_failures'] }} Gagal
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPI Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Attempts Today -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500/10 p-4 rounded-xl border border-blue-500/20">
                                <i class="fa-solid fa-fingerprint text-blue-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Percobaan Login (Hari Ini)</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-white">{{ $stats['total_attempts_today'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Successful Today -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-emerald-500/10 p-4 rounded-xl border border-emerald-500/20">
                                <i class="fa-solid fa-circle-check text-emerald-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Sukses Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-emerald-400">{{ $stats['successful_today'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Failed Today -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-rose-500/10 p-4 rounded-xl border border-rose-500/20">
                                <i class="fa-solid fa-circle-xmark text-rose-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Gagal Hari Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-rose-400">{{ $stats['failed_today'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 overflow-hidden rounded-2xl shadow-xl transition-all duration-300 hover:border-slate-600/50">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500/10 p-4 rounded-xl border border-indigo-500/20">
                                <i class="fa-solid fa-users-viewfinder text-indigo-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-400 truncate">Sesi Aktif Saat Ini</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-3xl font-semibold text-indigo-400">{{ $stats['active_sessions_count'] }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visualization: Charts & Top IP Sources -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Trend Chart (2/3 Column) -->
                <div class="lg:col-span-2 bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-6">
                    <h3 class="text-lg font-medium text-slate-200 mb-4 flex items-center">
                        <i class="fa-solid fa-chart-line mr-2 text-indigo-400"></i> Tren Percobaan Login (30 Hari Terakhir)
                    </h3>
                    <div id="login-trend-chart" class="w-full h-80"></div>
                </div>

                <!-- Top IP Sources (1/3 Column) -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-6 flex flex-col">
                    <h3 class="text-lg font-medium text-slate-200 mb-4 flex items-center">
                        <i class="fa-solid fa-list-ol mr-2 text-indigo-400"></i> Top 5 IP Address Teraktif
                    </h3>
                    @if($stats['top_ips']->isEmpty())
                        <div class="flex-1 flex flex-col items-center justify-center text-slate-400 py-8">
                            <i class="fa-solid fa-circle-info text-3xl text-slate-600 mb-2"></i>
                            <p class="text-sm">Belum ada data login terekam.</p>
                        </div>
                    @else
                        <div class="space-y-4 flex-1">
                            @foreach($stats['top_ips'] as $ip)
                                <div class="bg-slate-900/50 border border-slate-700/30 rounded-xl p-3 flex justify-between items-center hover:border-slate-600 transition-colors">
                                    <div class="flex items-center space-x-3">
                                        <div class="bg-slate-800 p-2 rounded-lg border border-slate-700">
                                            <i class="fa-solid fa-network-wired text-indigo-400"></i>
                                        </div>
                                        <div>
                                            <span class="font-mono text-sm text-white font-medium block">{{ $ip->ip_address }}</span>
                                            <span class="text-xs text-slate-400">Indonesia (Lokal)</span>
                                        </div>
                                    </div>
                                    <span class="bg-slate-800 text-slate-300 text-xs px-3 py-1 rounded-full font-semibold border border-slate-700/50">
                                        {{ $ip->count }} Request
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Security Audit Log Table -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-slate-200">
                        <i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-400"></i> Audit Trail: Percobaan Login Terbaru
                    </h3>
                    <span class="text-xs text-slate-400">Menampilkan 20 data terakhir</span>
                </div>
                
                @if($recentAttempts->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-shield text-4xl text-slate-600 mb-3 block"></i>
                        <p>Belum ada rekaman log percobaan login.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700/50">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Timestamp</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Identitas / Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">IP Address / Asal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Device & Browser</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Status Keamanan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 bg-slate-800/20">
                                @foreach($recentAttempts as $attempt)
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        <!-- Timestamp -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400">
                                            {{ $attempt->created_at->format('d M Y - H:i:s') }}
                                        </td>
                                        <!-- Identitas -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white flex items-center space-x-2">
                                                <i class="fa-solid fa-user-tag text-slate-400 text-xs"></i>
                                                <span>{{ $attempt->email }}</span>
                                            </div>
                                        </td>
                                        <!-- IP & Country -->
                                        <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                            <div class="text-slate-300 font-semibold">{{ $attempt->ip_address }}</div>
                                            <div class="text-xs text-slate-500 flex items-center mt-0.5">
                                                <i class="fa-solid fa-location-dot text-indigo-400 mr-1 text-[10px]"></i>
                                                {{ $attempt->country ?: 'Indonesia (Lokal)' }}
                                            </div>
                                        </td>
                                        <!-- User Agent Info -->
                                        <td class="px-6 py-4 max-w-xs">
                                            <div class="text-xs text-slate-400 truncate" title="{{ $attempt->user_agent }}">
                                                {{ $attempt->user_agent ?: 'Unknown User Agent' }}
                                            </div>
                                        </td>
                                        <!-- Security Status -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($attempt->successful)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                                                    Sukses Masuk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20" title="{{ $attempt->failure_reason }}">
                                                    <span class="h-2 w-2 rounded-full bg-rose-400 mr-2"></span>
                                                    Gagal: {{ $attempt->failure_reason ?: 'Kredensial salah' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ApexCharts Library Integration -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($trend);
            
            const options = {
                chart: {
                    type: 'area',
                    height: '100%',
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: '#94a3b8'
                },
                colors: ['#34d399', '#f87171'], // Success: emerald, Failure: rose
                series: [
                    { name: 'Login Sukses', data: chartData.success },
                    { name: 'Login Gagal', data: chartData.failure }
                ],
                xaxis: {
                    categories: chartData.dates.map(date => {
                        const d = new Date(date);
                        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                    }),
                    labels: { style: { colors: '#94a3b8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#94a3b8' } },
                },
                grid: {
                    borderColor: '#334155',
                    strokeDashArray: 4,
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: { show: true },
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: '#f1f5f9' }
                }
            };

            const chartElement = document.querySelector("#login-trend-chart");
            if (chartElement) {
                const chart = new ApexCharts(chartElement, options);
                chart.render();
            }
        });
    </script>
    @endpush
</x-app-layout>
