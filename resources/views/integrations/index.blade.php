<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300 tracking-tight leading-tight">
             Integrasi Sistem Pemerintah & Yayasan
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-950 text-gray-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-emerald-400 bg-emerald-950/40 border border-emerald-800/50 rounded-xl backdrop-blur-md shadow-lg shadow-emerald-950/20 flex items-center space-x-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-rose-400 bg-rose-950/40 border border-rose-800/50 rounded-xl backdrop-blur-md shadow-lg shadow-rose-950/20 flex items-center space-x-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- KPI Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Success Card -->
                <div class="relative overflow-hidden backdrop-blur-xl bg-white/5 dark:bg-gray-900/40 border border-emerald-500/20 rounded-3xl p-6 shadow-2xl transition-all duration-300 hover:border-emerald-500/40 group hover:translate-y-[-2px]">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-emerald-950/60 border border-emerald-800/30 rounded-2xl text-emerald-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-500">Sukses Ekspor / Sinkron</p>
                            <h3 class="text-3xl font-extrabold text-emerald-300 mt-1">{{ $totalSuccess }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Total Failed Card -->
                <div class="relative overflow-hidden backdrop-blur-xl bg-white/5 dark:bg-gray-900/40 border border-rose-500/20 rounded-3xl p-6 shadow-2xl transition-all duration-300 hover:border-rose-500/40 group hover:translate-y-[-2px]">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all duration-500"></div>
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-rose-950/60 border border-rose-800/30 rounded-2xl text-rose-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-500">Gagal Ekspor / Sinkron</p>
                            <h3 class="text-3xl font-extrabold text-rose-300 mt-1">{{ $totalFailed }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Active Webhooks Card -->
                <div class="relative overflow-hidden backdrop-blur-xl bg-white/5 dark:bg-gray-900/40 border border-cyan-500/20 rounded-3xl p-6 shadow-2xl transition-all duration-300 hover:border-cyan-500/40 group hover:translate-y-[-2px]">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-cyan-500/10 rounded-full blur-2xl group-hover:bg-cyan-500/20 transition-all duration-500"></div>
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-cyan-950/60 border border-cyan-800/30 rounded-2xl text-cyan-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-cyan-500">Webhook Outgoing Aktif</p>
                            <h3 class="text-3xl font-extrabold text-cyan-300 mt-1">{{ $webhooks->where('is_active', true)->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Provider Integration Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Dapodik Integrator -->
                <div class="backdrop-blur-xl bg-gray-900/60 border border-gray-800/80 rounded-3xl p-8 shadow-xl flex flex-col justify-between hover:shadow-emerald-950/10 transition-all duration-300">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800/30">Kemendikbud</span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-100">{{ $dapodik->name }}</h4>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">Ekspor data siswa, guru (PTK), sarana prasarana, rombel, dan absensi format impor resmi Dapodik pusat.</p>
                        
                        <div class="mt-6 space-y-2 text-xs text-gray-400">
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Status:</span>
                                <span class="text-emerald-400 font-bold">Terhubung</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Terakhir Ekspor:</span>
                                <span>{{ $dapodik->last_sync_at ? $dapodik->last_sync_at->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('admin.integrations.dapodik') }}" class="w-full inline-flex justify-center items-center py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 font-semibold text-sm rounded-2xl shadow-lg shadow-emerald-900/20 text-gray-950 transition-all duration-200">
                            Konfigurasi & Ekspor
                        </a>
                    </div>
                </div>

                <!-- EMIS Integrator -->
                <div class="backdrop-blur-xl bg-gray-900/60 border border-gray-800/80 rounded-3xl p-8 shadow-xl flex flex-col justify-between hover:shadow-cyan-950/10 transition-all duration-300">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-950 text-cyan-400 border border-cyan-800/30">Kemenag</span>
                            <span class="w-3 h-3 rounded-full bg-cyan-500 shadow-lg shadow-cyan-500/50"></span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-100">{{ $emis->name }}</h4>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">Penyelarasan data siswa, rombel, dan capaian keagamaan (tahfidz/mutaba'ah) resmi EMIS Kemenag.</p>
                        
                        <div class="mt-6 space-y-2 text-xs text-gray-400">
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Status:</span>
                                <span class="text-cyan-400 font-bold">Terhubung</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Terakhir Ekspor:</span>
                                <span>{{ $emis->last_sync_at ? $emis->last_sync_at->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('admin.integrations.emis') }}" class="w-full inline-flex justify-center items-center py-3 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 font-semibold text-sm rounded-2xl shadow-lg shadow-cyan-900/20 text-gray-950 transition-all duration-200">
                            Konfigurasi & Ekspor
                        </a>
                    </div>
                </div>

                <!-- Yayasan Integrator -->
                <div class="backdrop-blur-xl bg-gray-900/60 border border-gray-800/80 rounded-3xl p-8 shadow-xl flex flex-col justify-between opacity-80 hover:opacity-100 transition-all duration-300">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-gray-800 text-gray-300 border border-gray-700">Yayasan</span>
                            <span class="w-3 h-3 rounded-full bg-gray-600"></span>
                        </div>
                        <h4 class="text-xl font-bold text-gray-100">{{ $yayasan->name }}</h4>
                        <p class="text-sm text-gray-400 mt-3 leading-relaxed">Konektor sinkronisasi dinamis real-time dengan sistem database {{ school_foundation_name(false) }}.</p>
                        
                        <div class="mt-6 space-y-2 text-xs text-gray-400">
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Status:</span>
                                <span class="text-gray-500">Non-aktif</span>
                            </p>
                            <p class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-300">Terakhir Sync:</span>
                                <span>Belum dikonfigurasi</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-8">
                        <a href="{{ route('admin.integrations.webhooks.index') }}" class="w-full inline-flex justify-center items-center py-3 bg-gray-800 hover:bg-gray-700 font-semibold text-sm rounded-2xl shadow-lg text-gray-200 transition-all duration-200">
                            Kelola Webhooks
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: 10 Recent Integration Logs -->
            <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-bold text-gray-100 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Aktivitas Sinkronisasi Terakhir</span>
                    </h4>
                    <a href="{{ route('admin.integrations.logs') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition-colors">
                        Lihat Semua Log &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-xs font-bold uppercase tracking-wider text-gray-400">
                                <th class="pb-3">Provider</th>
                                <th class="pb-3">Waktu</th>
                                <th class="pb-3">Aksi</th>
                                <th class="pb-3 text-center">Proses</th>
                                <th class="pb-3 text-center">Gagal</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-800/10 transition-colors">
                                    <td class="py-4 font-semibold text-gray-200">{{ $log->config->name }}</td>
                                    <td class="py-4 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
                                    <td class="py-4 text-xs font-semibold">
                                        <span class="px-2.5 py-0.5 rounded-full bg-gray-800 border border-gray-700 text-gray-300">
                                            {{ strtoupper($log->direction) }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-center font-bold text-gray-300">{{ $log->records_processed }}</td>
                                    <td class="py-4 text-center font-bold text-rose-400">{{ $log->records_failed }}</td>
                                    <td class="py-4">
                                        @if($log->status === 'success')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/60 border border-emerald-800/40 text-emerald-400">
                                                Sukses
                                            </span>
                                        @elseif($log->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/60 border border-amber-800/40 text-amber-400">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-950/60 border border-rose-800/40 text-rose-400">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-xs text-gray-400 max-w-xs truncate">{{ $log->summary }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500 text-sm">
                                        Belum ada aktivitas sinkronisasi terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
