<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                <i class="fa-solid fa-gauge-high text-emerald-400 mr-2"></i> {{ __('Observabilitas & Kesehatan Sistem') }}
            </h2>
            <div class="flex space-x-2">
                <!-- Action Simulator Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 border border-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-all duration-200 flex items-center">
                        <i class="fa-solid fa-flask mr-2 text-indigo-400"></i> Simulator Tes <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-50 py-1" style="display: none;">
                        <form action="{{ route('admin.observability.simulate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="request">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 hover:text-white flex items-center">
                                <i class="fa-solid fa-spinner mr-2 text-amber-400 animate-spin"></i> Trigger Slow Request
                            </button>
                        </form>
                        <form action="{{ route('admin.observability.simulate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="query">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 hover:text-white flex items-center">
                                <i class="fa-solid fa-database mr-2 text-rose-400"></i> Trigger Slow Query
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Clear Logs Form -->
                <form action="{{ route('admin.observability.clear-logs') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh log performa & error?');">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-rose-600/90 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-lg shadow-rose-500/10 flex items-center">
                        <i class="fa-solid fa-trash-can mr-2"></i> Kosongkan Log
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
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

            <!-- METRIC CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-5">
                <!-- CPU Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">CPU LOAD</span>
                            <span class="bg-emerald-500/10 p-2 rounded-lg border border-emerald-500/20 text-emerald-400"><i class="fa-solid fa-microchip"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-2" id="cpu-value">{{ $health['cpu'] }}%</div>
                    </div>
                    <div class="w-full bg-slate-900 rounded-full h-1.5 mt-2">
                        <div class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500" id="cpu-bar" style="width: {{ $health['cpu'] }}%"></div>
                    </div>
                </div>

                <!-- RAM Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">MEMORI</span>
                            <span class="bg-blue-500/10 p-2 rounded-lg border border-blue-500/20 text-blue-400"><i class="fa-solid fa-memory"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="ram-value">{{ $health['ram']['percentage'] }}%</div>
                        <div class="text-[10px] text-slate-400 font-mono" id="ram-detail">{{ $health['ram']['used'] }} GB / {{ $health['ram']['total'] }} GB</div>
                    </div>
                    <div class="w-full bg-slate-900 rounded-full h-1.5 mt-2">
                        <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500" id="ram-bar" style="width: {{ $health['ram']['percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Disk Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">DISK SPACE</span>
                            <span class="bg-indigo-500/10 p-2 rounded-lg border border-indigo-500/20 text-indigo-400"><i class="fa-solid fa-hard-drive"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="disk-value">{{ $health['disk']['percentage'] }}%</div>
                        <div class="text-[10px] text-slate-400 font-mono" id="disk-detail">{{ $health['disk']['used'] }} GB / {{ $health['disk']['total'] }} GB</div>
                    </div>
                    <div class="w-full bg-slate-900 rounded-full h-1.5 mt-2">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" id="disk-bar" style="width: {{ $health['disk']['percentage'] }}%"></div>
                    </div>
                </div>

                <!-- DB Size Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">DB SIZE</span>
                            <span class="bg-amber-500/10 p-2 rounded-lg border border-amber-500/20 text-amber-400"><i class="fa-solid fa-database"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="db-size-value">{{ $health['db_size'] }}</div>
                        <div class="text-[10px] text-slate-400">SQLite Database</div>
                    </div>
                    <div class="text-[10px] text-emerald-400 font-semibold mt-2 flex items-center">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Koneksi SQLite OK
                    </div>
                </div>

                <!-- Active Users Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">USER AKTIF</span>
                            <span class="bg-purple-500/10 p-2 rounded-lg border border-purple-500/20 text-purple-400"><i class="fa-solid fa-users"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="active-users-value">{{ $health['active_users'] }}</div>
                        <div class="text-[10px] text-slate-400">Online 15 Menit Terakhir</div>
                    </div>
                    <div class="text-[10px] text-slate-400 mt-2">
                        Distinct Auth Users
                    </div>
                </div>

                <!-- Queue Depth Metric Card -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-5 hover:border-slate-600/50 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">QUEUE DEPTH</span>
                            <span class="bg-rose-500/10 p-2 rounded-lg border border-rose-500/20 text-rose-400"><i class="fa-solid fa-list-check"></i></span>
                        </div>
                        <div class="text-2xl font-bold text-white mb-1" id="queue-depth-value">{{ $health['queue_depth'] }}</div>
                        <div class="text-[10px] text-slate-400">Pending Jobs</div>
                    </div>
                    <div class="text-[10px] text-slate-400 mt-2 flex justify-between items-center">
                        <span>Database Queue</span>
                        @if($health['queue_depth'] > 0)
                            <a href="{{ route('admin.queue.index') }}" class="text-[10px] text-indigo-400 hover:text-indigo-300 font-semibold">Monitor</a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- LIVE HARDWARE MONITOR CHART -->
            <div class="grid grid-cols-1 gap-6">
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-slate-200 flex items-center">
                            <i class="fa-solid fa-chart-line mr-2 text-emerald-400"></i> Hardware Monitor (Real-time)
                        </h3>
                        <span class="bg-slate-900 border border-slate-700 text-slate-400 text-xs px-3 py-1 rounded-lg flex items-center font-semibold">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span> Live Polling (3s)
                        </span>
                    </div>
                    <div class="w-full h-80 relative">
                        <div id="live-metrics-chart" class="w-full h-full"></div>
                    </div>
                </div>
            </div>

            <!-- LOGS & FEEDS AREA (TABS INTERAKTIF) -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden" x-data="{ tab: 'requests' }">
                <!-- Navigation Tabs -->
                <div class="px-6 py-4 border-b border-slate-700/50 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div class="flex space-x-2">
                        <button @click="tab = 'requests'" :class="tab === 'requests' ? 'bg-indigo-600 text-white' : 'bg-slate-700/50 text-slate-400 hover:text-slate-200'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center">
                            <i class="fa-solid fa-spinner mr-2 text-xs"></i> Slow Requests
                            <span class="ml-2 bg-slate-900/50 text-xs text-slate-300 px-2 py-0.5 rounded-full font-bold">{{ count($slowRequests) }}</span>
                        </button>
                        <button @click="tab = 'queries'" :class="tab === 'queries' ? 'bg-indigo-600 text-white' : 'bg-slate-700/50 text-slate-400 hover:text-slate-200'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center">
                            <i class="fa-solid fa-database mr-2 text-xs"></i> Slow Queries
                            <span class="ml-2 bg-slate-900/50 text-xs text-slate-300 px-2 py-0.5 rounded-full font-bold">{{ count($slowQueries) }}</span>
                        </button>
                        <button @click="tab = 'errors'" :class="tab === 'errors' ? 'bg-indigo-600 text-white' : 'bg-slate-700/50 text-slate-400 hover:text-slate-200'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center">
                            <i class="fa-solid fa-bug mr-2 text-xs"></i> Log Error System
                            <span class="ml-2 bg-rose-500/20 text-xs text-rose-300 px-2 py-0.5 rounded-full font-bold">{{ $errorCount24h }} (24h)</span>
                        </button>
                    </div>
                    <span class="text-xs text-slate-500 font-mono">Log Source: storage/logs/observability.json</span>
                </div>

                <!-- Tab Content: Slow Requests -->
                <div x-show="tab === 'requests'" class="p-0">
                    @if(empty($slowRequests))
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-circle-info text-3xl text-slate-600 mb-3 block"></i>
                            <p>Tidak ada slow requests terdeteksi.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-700/50">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Timestamp</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Method & URL</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Duration</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Memory Peak</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">User & Queries</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50 bg-slate-800/20">
                                    @foreach($slowRequests as $req)
                                        <tr class="hover:bg-slate-700/10 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                                {{ \Carbon\Carbon::parse($req['timestamp'])->format('d M Y - H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $req['method'] === 'POST' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                                                        {{ $req['method'] }}
                                                    </span>
                                                    <span class="text-sm font-semibold text-slate-200 block max-w-lg truncate" title="{{ $req['url'] }}">{{ parse_url($req['url'], PHP_URL_PATH) }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="text-rose-400 font-semibold font-mono">{{ number_format($req['duration_ms']) }} ms</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300 font-mono">
                                                {{ $req['memory_mb'] }} MB
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                                                <div class="flex flex-col space-y-1">
                                                    <span class="flex items-center"><i class="fa-solid fa-user text-[10px] mr-1.5 text-slate-500"></i> User ID: {{ $req['user_id'] ?? 'Guest' }}</span>
                                                    @if(!empty($req['slow_queries']))
                                                        <span class="text-rose-400 font-semibold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ count($req['slow_queries']) }} Slow Queries</span>
                                                    @else
                                                        <span class="text-slate-500"><i class="fa-solid fa-circle-check text-emerald-500 text-[10px] mr-1"></i> 0 Slow Queries</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab Content: Slow Queries -->
                <div x-show="tab === 'queries'" class="p-0" style="display: none;">
                    @if(empty($slowQueries))
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-circle-info text-3xl text-slate-600 mb-3 block"></i>
                            <p>Tidak ada slow queries terdeteksi.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-700/50">
                                <thead class="bg-slate-900/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-36">Timestamp</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-24">Duration</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">SQL Query</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Origin URL</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-700/50 bg-slate-800/20">
                                    @foreach($slowQueries as $q)
                                        <tr class="hover:bg-slate-700/10 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                                {{ \Carbon\Carbon::parse($q['timestamp'])->format('d M Y - H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="text-amber-400 font-semibold font-mono">{{ number_format($q['time_ms']) }} ms</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="bg-slate-950/80 border border-slate-700/30 rounded-lg p-2.5 max-w-2xl font-mono text-xs text-emerald-400 overflow-x-auto">
                                                    <code>{{ $q['sql'] }}</code>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                                {{ parse_url($q['url'], PHP_URL_PATH) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tab Content: Error Logs -->
                <div x-show="tab === 'errors'" class="p-6" style="display: none;">
                    @if(empty($errors))
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-circle-info text-3xl text-slate-600 mb-3 block"></i>
                            <p>Tidak ada log error system terekam dalam 24 jam terakhir.</p>
                        </div>
                    @else
                        <!-- Terminal Log Stream -->
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 shadow-inner max-h-[500px] overflow-y-auto font-mono text-xs text-slate-300 space-y-3">
                            @foreach($errors as $err)
                                <div class="p-3 border-l-2 {{ in_array($err['level'], ['emergency', 'alert', 'critical']) ? 'border-rose-500 bg-rose-950/10' : 'border-amber-500 bg-amber-950/10' }} rounded-r">
                                    <div class="flex justify-between items-center text-[10px] text-slate-500 mb-1">
                                        <span class="font-bold flex items-center">
                                            <i class="fa-solid fa-circle text-rose-500 mr-1.5 animate-pulse"></i> 
                                            [{{ strtoupper($err['level']) }}]
                                        </span>
                                        <span>{{ \Carbon\Carbon::parse($err['timestamp'])->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                    <div class="text-slate-100 font-semibold mb-1">{{ $err['message'] }}</div>
                                    
                                    @if(!empty($err['context']))
                                        <div class="text-[10px] text-slate-400 bg-slate-900/50 p-2 rounded mt-2 overflow-x-auto max-h-40">
                                            <pre><code>{{ json_encode($err['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                        </div>
                                    @endif

                                    <div class="mt-2 text-[10px] text-slate-500 flex justify-between items-center">
                                        <span>Origin URL: <span class="text-slate-400">{{ $err['url'] }}</span></span>
                                        <span>User ID: <span class="text-slate-400">{{ $err['user_id'] ?? 'Guest' }}</span></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- ACTION QUICK LINKS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Monitor Keamanan SIEM Link -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-6 flex justify-between items-center hover:border-indigo-500/30 transition-all duration-300">
                    <div class="flex items-center space-x-4">
                        <div class="bg-indigo-500/10 p-4 rounded-xl border border-indigo-500/20 text-indigo-400 text-2xl">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-base">Dashboard Keamanan SIEM</h4>
                            <p class="text-xs text-slate-400 mt-1">Audit trail login, deteksi brute-force, dan terminasi sesi aktif user.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.security.dashboard') }}" class="p-3 bg-slate-700 hover:bg-indigo-600 rounded-xl text-white transition-all"><i class="fa-solid fa-arrow-right"></i></a>
                </div>

                <!-- Monitor Queue Link -->
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl p-6 flex justify-between items-center hover:border-emerald-500/30 transition-all duration-300">
                    <div class="flex items-center space-x-4">
                        <div class="bg-emerald-500/10 p-4 rounded-xl border border-emerald-500/20 text-emerald-400 text-2xl">
                            <i class="fa-solid fa-list-check"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-base">Monitor Queue & Background Jobs</h4>
                            <p class="text-xs text-slate-400 mt-1">Status real-time jobs antrean, percobaan ulang kegagalan, dan pengosongan queue.</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.queue.index') }}" class="p-3 bg-slate-700 hover:bg-emerald-600 rounded-xl text-white transition-all"><i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>

        </div>
    </div>

    <!-- Live Performance ApexCharts Script -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Live shifting chart variables
            const maxPoints = 20;
            let cpuData = Array(maxPoints).fill(0);
            let ramData = Array(maxPoints).fill(0);
            let timeLabels = Array(maxPoints).fill('');

            // Set current initial state
            cpuData[maxPoints - 1] = {{ $health['cpu'] }};
            ramData[maxPoints - 1] = {{ $health['ram']['percentage'] }};
            timeLabels[maxPoints - 1] = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            const options = {
                chart: {
                    type: 'line',
                    height: '100%',
                    animations: {
                        enabled: true,
                        easing: 'linear',
                        dynamicAnimation: {
                            speed: 1000
                        }
                    },
                    toolbar: { show: false },
                    background: 'transparent',
                    foreColor: '#94a3b8'
                },
                colors: ['#10b981', '#3b82f6'], // CPU: Emerald, RAM: Blue
                series: [
                    { name: 'CPU Load (%)', data: cpuData },
                    { name: 'RAM Usage (%)', data: ramData }
                ],
                xaxis: {
                    categories: timeLabels,
                    labels: { show: false },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    max: 100,
                    min: 0,
                    labels: { style: { colors: '#94a3b8' } }
                },
                grid: {
                    borderColor: '#334155',
                    strokeDashArray: 4,
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                tooltip: {
                    theme: 'dark',
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: '#f1f5f9' }
                }
            };

            const liveChart = new ApexCharts(document.querySelector("#live-metrics-chart"), options);
            liveChart.render();

            // Dynamic polling execution every 3 seconds
            setInterval(function () {
                fetch('{{ route("admin.observability.metrics") }}')
                    .then(response => {
                        if (!response.ok) throw new Error("HTTP error " + response.status);
                        return response.json();
                    })
                    .then(data => {
                        const now = new Date();
                        const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        
                        // Append and shift
                        cpuData.push(data.cpu);
                        ramData.push(data.ram.percentage);
                        timeLabels.push(timeStr);
                        
                        if (cpuData.length > maxPoints) {
                            cpuData.shift();
                            ramData.shift();
                            timeLabels.shift();
                        }
                        
                        // Refresh live chart series
                        liveChart.updateSeries([
                            { name: 'CPU Load (%)', data: cpuData },
                            { name: 'RAM Usage (%)', data: ramData }
                        ]);

                        // Update individual card DOM text & gauge width values
                        document.querySelector("#cpu-value").innerText = data.cpu + '%';
                        document.querySelector("#cpu-bar").style.width = data.cpu + '%';

                        document.querySelector("#ram-value").innerText = data.ram.percentage + '%';
                        document.querySelector("#ram-bar").style.width = data.ram.percentage + '%';
                        document.querySelector("#ram-detail").innerText = data.ram.used + ' GB / ' + data.ram.total + ' GB';

                        document.querySelector("#disk-value").innerText = data.disk.percentage + '%';
                        document.querySelector("#disk-bar").style.width = data.disk.percentage + '%';
                        document.querySelector("#disk-detail").innerText = data.disk.used + ' GB / ' + data.disk.total + ' GB';

                        document.querySelector("#db-size-value").innerText = data.db_size;
                        document.querySelector("#active-users-value").innerText = data.active_users;
                        document.querySelector("#queue-depth-value").innerText = data.queue_depth;
                    })
                    .catch(err => console.warn("Failed fetching live observability metrics:", err));
            }, 3000);
        });
    </script>
    @endpush
</x-app-layout>
