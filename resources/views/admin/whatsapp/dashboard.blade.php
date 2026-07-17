<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    {{ __('WhatsApp Gateway Dashboard') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pantau statistik pengiriman, efisiensi pesan, tren log harian, dan pintasan otomatisasi.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/30 rounded-2xl text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/30 rounded-2xl text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center shadow-sm">
                    <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="flex flex-wrap gap-2 bg-gray-100/50 dark:bg-gray-900/50 p-1.5 rounded-2xl border border-gray-100 dark:border-gray-800 max-w-max">
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.dashboard') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.templates.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Templates
                </a>
                <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.broadcasts.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Broadcasts
                </a>
                <a href="{{ route('admin.whatsapp.automations.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.automations.*') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Automations
                </a>
                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.index') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Settings & Simulator
                </a>
                <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ request()->routeIs('admin.whatsapp.logs') ? 'bg-teal-600 text-white shadow-sm shadow-teal-500/10' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-gray-800' }}">
                    Logs
                </a>
            </div>

            <!-- Statistics Grid (6 Cards) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Total Logs -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-slate-50 dark:bg-slate-900 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Total Logs</span>
                        <span class="text-xl font-black text-slate-800 dark:text-white mt-1.5 block">{{ number_format($stats['total']) }}</span>
                    </div>
                </div>

                <!-- Sukses -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Sukses</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1.5 block">{{ number_format($stats['sent']) }}</span>
                    </div>
                </div>

                <!-- Gagal -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-rose-50 dark:bg-rose-950/30 rounded-xl flex items-center justify-center text-rose-600 dark:text-rose-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Gagal</span>
                        <span class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1.5 block">{{ number_format($stats['failed']) }}</span>
                    </div>
                </div>

                <!-- Rasio Sukses -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-teal-50 dark:bg-teal-950/30 rounded-xl flex items-center justify-center text-teal-600 dark:text-teal-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Rasio Sukses</span>
                        <span class="text-xl font-black text-teal-600 dark:text-teal-400 mt-1.5 block">{{ $stats['success_rate'] }}%</span>
                    </div>
                </div>

                <!-- Terkirim Hari Ini -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 rounded-xl flex items-center justify-center text-amber-600 dark:text-amber-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Hari Ini</span>
                        <span class="text-xl font-black text-slate-800 dark:text-white mt-1.5 block">{{ number_format($stats['sent_today']) }}</span>
                    </div>
                </div>

                <!-- Terkirim Bulan Ini -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between hover:-translate-y-0.5 hover:shadow-md transition-all duration-300">
                    <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-none">Bulan Ini</span>
                        <span class="text-xl font-black text-slate-800 dark:text-white mt-1.5 block">{{ number_format($stats['sent_this_month']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Panel Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Chart Area (2 Columns) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">Tren Pengiriman</h3>
                                <p class="text-xs text-gray-400 font-bold">7 Hari Terakhir</p>
                            </div>
                            <div class="flex items-center gap-4 text-xs font-bold">
                                <span class="flex items-center gap-1.5 text-gray-500">
                                    <span class="w-3 h-3 rounded-md bg-teal-500 block"></span> Sukses
                                </span>
                                <span class="flex items-center gap-1.5 text-gray-500">
                                    <span class="w-3 h-3 rounded-md bg-rose-500 block"></span> Gagal
                                </span>
                            </div>
                        </div>

                        <!-- Custom Scaled Visual Bar Chart in Blade -->
                        @php
                            $maxVal = 1;
                            foreach ($chartData['sent'] as $k => $sentVal) {
                                $total = $sentVal + $chartData['failed'][$k];
                                if ($total > $maxVal) {
                                    $maxVal = $total;
                                }
                            }
                            // Add extra padding to chart top
                            $maxVal = ceil($maxVal * 1.15);
                        @endphp

                        <div class="h-64 flex items-end justify-between gap-2 pt-6 border-b border-gray-100 dark:border-gray-700 relative">
                            <!-- Y-axis Guidelines -->
                            <div class="absolute inset-x-0 top-0 border-t border-dashed border-gray-100 dark:border-gray-800/40 text-[9px] text-gray-400 font-black tracking-wider pt-1">
                                {{ number_format($maxVal) }} (Maks)
                            </div>
                            <div class="absolute inset-x-0 top-1/2 border-t border-dashed border-gray-100 dark:border-gray-800/40 text-[9px] text-gray-400 font-black tracking-wider pt-1">
                                {{ number_format($maxVal / 2) }}
                            </div>

                            @foreach($chartData['labels'] as $index => $label)
                                @php
                                    $sentCount = $chartData['sent'][$index];
                                    $failedCount = $chartData['failed'][$index];
                                    $totalDay = $sentCount + $failedCount;

                                    $sentPct = $totalDay > 0 ? ($sentCount / $maxVal) * 100 : 0;
                                    $failedPct = $totalDay > 0 ? ($failedCount / $maxVal) * 100 : 0;
                                @endphp
                                <div class="flex-1 flex flex-col items-center group relative z-10">
                                    <!-- Tooltip -->
                                    <div class="absolute bottom-full mb-2 bg-slate-900 text-white text-[10px] font-black rounded-lg p-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-xl pointer-events-none text-center min-w-[80px]">
                                        <p class="text-teal-400">Sukses: {{ $sentCount }}</p>
                                        <p class="text-rose-400">Gagal: {{ $failedCount }}</p>
                                        <hr class="border-gray-700 my-1">
                                        <p class="text-gray-300">Total: {{ $totalDay }}</p>
                                    </div>

                                    <!-- Bars Container -->
                                    <div class="w-full flex items-end justify-center gap-1.5 h-48">
                                        <!-- Sent Bar (Teal) -->
                                        <div class="w-4 sm:w-6 bg-teal-500 dark:bg-teal-600 rounded-t-lg transition-all duration-500 hover:brightness-105" 
                                             style="height: {{ max($sentPct, 3) }}%"></div>
                                        <!-- Failed Bar (Rose) -->
                                        <div class="w-4 sm:w-6 bg-rose-500 dark:bg-rose-600 rounded-t-lg transition-all duration-500 hover:brightness-105" 
                                             style="height: {{ max($failedPct, $failedCount > 0 ? 3 : 0) }}%"></div>
                                    </div>

                                    <!-- Label -->
                                    <span class="text-[10px] font-black text-gray-400 mt-2 uppercase tracking-wider block">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-400 font-medium">
                        <span>Grafik di atas dirender dinamis berdasarkan data log riil.</span>
                        <a href="{{ route('admin.whatsapp.logs') }}" class="text-teal-600 hover:text-teal-700 font-bold uppercase tracking-wider">Lihat Semua Log &rarr;</a>
                    </div>
                </div>

                <!-- Quick Action Card (1 Column) -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider mb-6">Pintasan Aksi</h3>
                        
                        <div class="space-y-4">
                            <!-- Siaran Baru -->
                            <a href="{{ route('admin.whatsapp.broadcasts.create') }}" class="group flex items-center p-4 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-100/50 dark:border-teal-900/30 hover:scale-[1.01] hover:-translate-y-0.5 transition-all duration-300">
                                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md shadow-teal-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xs font-black text-teal-800 dark:text-teal-300 uppercase tracking-wider">Buat Siaran Baru</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium mt-0.5">Kirim pesan massal ke wali kelas, guru, dsb.</p>
                                </div>
                            </a>

                            <!-- Kelola Template -->
                            <a href="{{ route('admin.whatsapp.templates.index') }}" class="group flex items-center p-4 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100/50 dark:border-indigo-900/30 hover:scale-[1.01] hover:-translate-y-0.5 transition-all duration-300">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md shadow-indigo-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xs font-black text-indigo-800 dark:text-indigo-300 uppercase tracking-wider">Kelola Template</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium mt-0.5">Buat, edit, dan atur penampung pesan dinamis.</p>
                                </div>
                            </a>

                            <!-- Aturan Otomasi -->
                            <a href="{{ route('admin.whatsapp.automations.index') }}" class="group flex items-center p-4 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100/50 dark:border-amber-900/30 hover:scale-[1.01] hover:-translate-y-0.5 transition-all duration-300">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center group-hover:scale-110 transition-transform shadow-md shadow-amber-500/20">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xs font-black text-amber-800 dark:text-amber-300 uppercase tracking-wider font-semibold">Aturan Otomasi</h4>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium mt-0.5">Konfigurasi pemicu SPP overdue, alpa, UKS.</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs font-bold text-gray-500">
                        <span>Pemicu Aktif</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900">
                            Fonnte Enabled
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
