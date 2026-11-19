<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white tracking-tight leading-tight">
                    Integrasi Sistem Pemerintah &amp; Yayasan
                </h2>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">
                    Sinkronisasi resmi Dapodik, EMIS Kemenag, antrean pembaruan, dan webhook layanan eksternal.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.integrations.logs') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Log Audit Integrasi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 text-sm text-emerald-800 dark:text-emerald-200 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center gap-2.5">
                    <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 text-sm text-rose-800 dark:text-rose-200 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl flex items-center gap-2.5">
                    <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- KPI Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Sukses Ekspor / Sinkron</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $totalSuccess }}</h3>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800/60 rounded-xl text-rose-600 dark:text-rose-400 shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">Gagal Ekspor / Sinkron</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $totalFailed }}</h3>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm flex items-center gap-4">
                    <div class="p-3 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/60 rounded-xl text-cyan-600 dark:text-cyan-400 shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-700 dark:text-cyan-400">Webhook Outgoing Aktif</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $webhooks->where('is_active', true)->count() }}</h3>
                    </div>
                </div>
            </div>

            <!-- Provider Integration Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Dapodik Integrator -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">Kemendikdasmen</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $dapodik->name }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">Ekspor data siswa, guru (PTK), sarana prasarana, rombel, dan absensi format impor resmi Dapodik pusat.</p>

                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Status:</span>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Terhubung</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Terakhir Ekspor:</span>
                                <span>{{ $dapodik->last_sync_at ? $dapodik->last_sync_at->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col gap-2">
                        <a href="{{ route('admin.integrations.dapodik') }}" class="w-full inline-flex justify-center items-center py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 font-semibold text-sm rounded-xl text-white transition-colors">
                            Konfigurasi &amp; Ekspor
                        </a>
                        @if(Route::has('admin.integrations.dapodik.sync-queue'))
                            <a href="{{ route('admin.integrations.dapodik.sync-queue') }}" class="w-full inline-flex justify-center items-center py-2 px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-semibold text-xs rounded-xl text-slate-700 dark:text-slate-200 transition-colors">
                                Antrean Update Dapodik
                            </a>
                        @endif
                    </div>
                </div>

                <!-- EMIS Integrator -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800/60">Kemenag</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $emis->name }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">Penyelarasan data siswa, rombel, dan capaian keagamaan (tahfidz/mutaba'ah) resmi EMIS Kemenag.</p>

                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Status:</span>
                                <span class="text-cyan-600 dark:text-cyan-400 font-bold">Terhubung</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Terakhir Ekspor:</span>
                                <span>{{ $emis->last_sync_at ? $emis->last_sync_at->diffForHumans() : 'Belum pernah' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.integrations.emis') }}" class="w-full inline-flex justify-center items-center py-2.5 px-4 bg-cyan-600 hover:bg-cyan-700 font-semibold text-sm rounded-xl text-white transition-colors">
                            Konfigurasi &amp; Ekspor
                        </a>
                    </div>
                </div>

                <!-- Yayasan Integrator -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">Yayasan</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $yayasan->name }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">Konektor sinkronisasi real-time dengan sistem database {{ school_foundation_name(false) }}.</p>

                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Status:</span>
                                <span class="text-slate-500 dark:text-slate-400">Siap dikonfigurasi</span>
                            </p>
                            <p class="flex items-center gap-2">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">Terakhir Sync:</span>
                                <span>Belum dikonfigurasi</span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('admin.integrations.webhooks.index') }}" class="w-full inline-flex justify-center items-center py-2.5 px-4 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 font-semibold text-sm rounded-xl text-white transition-colors">
                            Kelola Webhooks
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: 10 Recent Integration Logs -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Aktivitas Sinkronisasi Terakhir</span>
                    </h4>
                    <a href="{{ route('admin.integrations.logs') }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-400 hover:underline">
                        Lihat Semua Log &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="pb-3 text-start">Provider</th>
                                <th class="pb-3 text-start">Waktu</th>
                                <th class="pb-3 text-start">Aksi</th>
                                <th class="pb-3 text-center">Proses</th>
                                <th class="pb-3 text-center">Gagal</th>
                                <th class="pb-3 text-start">Status</th>
                                <th class="pb-3 text-start">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $log->config->name }}</td>
                                    <td class="py-3.5 text-xs text-slate-500 dark:text-slate-400">{{ $log->created_at->diffForHumans() }}</td>
                                    <td class="py-3.5 text-xs font-semibold">
                                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200">
                                            {{ strtoupper($log->direction) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 text-center font-bold text-slate-700 dark:text-slate-200">{{ $log->records_processed }}</td>
                                    <td class="py-3.5 text-center font-bold text-rose-600 dark:text-rose-400">{{ $log->records_failed }}</td>
                                    <td class="py-3.5">
                                        @if($log->status === 'success')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300">
                                                Sukses
                                            </span>
                                        @elseif($log->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/50 text-amber-700 dark:text-amber-300">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate">{{ $log->summary }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
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
