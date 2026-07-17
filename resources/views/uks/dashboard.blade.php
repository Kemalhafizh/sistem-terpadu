<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </span>
                    {{ __('UKS Digital (Unit Kesehatan Sekolah)') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • KESISWAAN & KESEHATAN</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('uks.visits.create') }}" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Catat Kunjungan Baru
                </a>
                
                <button type="button" @click="$dispatch('open-modal', 'export-monthly-modal')" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Laporan PDF Bulanan
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-center text-teal-800 dark:text-teal-300">
                    <svg class="w-5 h-5 mr-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- 4-Grid Statistik Panel -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat 1: Kunjungan Hari Ini -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-teal-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Kunjungan Hari Ini</p>
                        <h3 class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $todayVisitsCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Siswa masuk UKS</p>
                    </div>
                    <div class="w-14 h-14 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 2: Kasus Darurat / Berat -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-red-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Kasus Berat Hari Ini</p>
                        <h3 class="text-4xl font-extrabold text-red-600 dark:text-red-500">{{ $criticalCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Butuh perhatian darurat</p>
                    </div>
                    <div class="w-14 h-14 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 3: Obat Hampir Habis -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-amber-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Stok Obat Menipis</p>
                        <h3 class="text-4xl font-extrabold text-amber-600 dark:text-amber-500">{{ $lowStockCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Kuantitas di bawah batas</p>
                    </div>
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 4: Kadaluwarsa Dekat -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-yellow-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Kadaluwarsa Terdekat</p>
                        <h3 class="text-4xl font-extrabold text-yellow-600 dark:text-yellow-500">{{ $expiringSoonCount }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Dalam tempo 30 hari</p>
                    </div>
                    <div class="w-14 h-14 bg-yellow-50 dark:bg-yellow-950/30 text-yellow-600 dark:text-yellow-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Menu -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Link: Rekam Medis -->
                <a href="{{ route('uks.health-records.index') }}" class="bg-gradient-to-br from-teal-500 to-emerald-600 hover:from-teal-600 hover:to-emerald-700 text-white rounded-3xl p-6 shadow-md transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-between group">
                    <div>
                        <h4 class="font-extrabold text-lg">Rekam Medis Siswa</h4>
                        <p class="text-xs text-teal-100 mt-1">Alergi, penyakit kronis & riwayat medis</p>
                    </div>
                    <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </a>

                <!-- Link: Health Screening -->
                <a href="{{ route('uks.screenings.index') }}" class="bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-3xl p-6 shadow-md transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-between group">
                    <div>
                        <h4 class="font-extrabold text-lg">Health Screening</h4>
                        <p class="text-xs text-blue-100 mt-1">Pemeriksaan berkala & grafik tinggi/berat</p>
                    </div>
                    <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                </a>

                <!-- Link: Log Pemberian Obat -->
                <a href="{{ route('uks.medicines.dispense-log') }}" class="bg-gradient-to-br from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-3xl p-6 shadow-md transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-between group">
                    <div>
                        <h4 class="font-extrabold text-lg">Log Pemberian Obat</h4>
                        <p class="text-xs text-indigo-100 mt-1">Log pemberian resep & obat ke siswa</p>
                    </div>
                    <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </a>

                <!-- Link: Log Restock Obat -->
                <a href="{{ route('uks.medicines.restock-log') }}" class="bg-gradient-to-br from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white rounded-3xl p-6 shadow-md transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-between group">
                    <div>
                        <h4 class="font-extrabold text-lg">Log Restock Obat</h4>
                        <p class="text-xs text-purple-100 mt-1">Log restock & penambahan obat baru</p>
                    </div>
                    <div class="w-12 h-12 bg-white/15 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                </a>
            </div>

            <!-- Grafik Tren & Sebaran (ApexCharts) -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Tren & Keparahan Kunjungan Medis</h4>
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Evaluasi Tren 7 Hari Terakhir</p>
                    </div>
                    <div class="w-10 h-10 bg-teal-50 dark:bg-teal-950/30 rounded-xl flex items-center justify-center text-teal-600 dark:text-teal-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                
                <div id="uksWeeklyChart" class="w-full h-80"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Panel Kiri: Timeline Kunjungan Terkini (2/3) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                            Kunjungan Terkini
                        </h4>
                        <a href="{{ route('uks.visits.index') }}" class="text-xs font-black text-teal-600 dark:text-teal-400 hover:underline">LIHAT SEMUA →</a>
                    </div>

                    <div class="flex-1 overflow-y-auto space-y-6">
                        @forelse($recentVisits as $visit)
                            <div class="p-5 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 group transition-all hover:border-teal-500/30">
                                <div class="flex items-start">
                                    <div class="w-12 h-12 bg-teal-100 dark:bg-teal-950/50 text-teal-700 dark:text-teal-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-lg mr-4">
                                        {{ substr($visit->student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $visit->student->user->name }}</span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">({{ $visit->student->classroom->name ?? 'Tidak ada kelas' }})</span>
                                        </div>
                                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 mt-1">Keluhan: <span class="text-teal-600 dark:text-teal-400">"{{ $visit->complaint }}"</span></p>
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">
                                            {{ $visit->visit_time->translatedFormat('d M Y • H:i') }} WIB • Ditangani: {{ $visit->handler->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                                    <!-- Severity Badge -->
                                    @if($visit->severity === 'berat')
                                        <span class="px-2.5 py-1 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Berat</span>
                                    @elseif($visit->severity === 'sedang')
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Sedang</span>
                                    @else
                                        <span class="px-2.5 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Ringan</span>
                                    @endif

                                    <!-- Disposition Badge -->
                                    @php
                                        $dispLabels = [
                                            'kembali_kelas' => ['bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30', 'Kembali Kelas'],
                                            'istirahat_uks' => ['bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-900/30', 'Istirahat UKS'],
                                            'pulang' => ['bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900/30', 'Pulang'],
                                            'rujuk_rs' => ['bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-900/30', 'Rujuk RS']
                                        ];
                                        $disp = $dispLabels[$visit->disposition] ?? ['bg-gray-50 text-gray-700', 'Istirahat'];
                                    @endphp
                                    <span class="px-2.5 py-1 {{ $disp[0] }} border rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $disp[1] }}</span>
                                    
                                    <a href="{{ route('uks.visits.show', $visit->uuid) }}" class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-300 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center opacity-50">
                                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Belum ada kunjungan siswa hari ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel Kanan: Obat Stok Rendah / Kadaluarsa (1/3) -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-2 h-6 bg-amber-500 rounded-full mr-3"></span>
                            Stok Kritis & Kadaluwarsa
                        </h4>
                        <a href="{{ route('uks.medicines.index') }}" class="text-xs font-black text-amber-500 hover:underline">LIHAT INVENTARIS →</a>
                    </div>

                    <div class="flex-grow space-y-5">
                        @forelse($lowStockMedicines as $med)
                            @php
                                $stockPct = $med->stock > 0 ? min(100, max(0, ($med->stock / ($med->min_stock * 2)) * 100)) : 0;
                                $colorClass = $med->stock <= $med->min_stock ? 'bg-red-500' : 'bg-amber-500';
                            @endphp
                            <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl space-y-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h5 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $med->name }}</h5>
                                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 mt-0.5 uppercase tracking-wider">{{ $med->category }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400">
                                        Stok: {{ $med->stock }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $colorClass }}" style="width: {{ $stockPct }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[10px] font-bold text-gray-400 dark:text-gray-500">
                                    <span>Min: {{ $med->min_stock }}</span>
                                    @if($med->expiry_date)
                                        <span class="{{ $med->expiry_date->isPast() ? 'text-red-500 font-extrabold' : ($med->expiry_date->diffInDays(now()) < 30 ? 'text-amber-500 font-extrabold' : '') }}">
                                            Exp: {{ $med->expiry_date->translatedFormat('d/m/y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center opacity-50">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs font-bold text-gray-500 dark:text-gray-400">Semua obat dalam kondisi stok aman.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Ekspor Rekap Bulanan -->
    <x-modal name="export-monthly-modal" focusable>
        <form method="GET" action="{{ route('uks.export.monthly') }}" class="p-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Ekspor Laporan Bulanan UKS</h3>
            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Pilih rentang bulan untuk mengunduh laporan PDF resmi.</p>
            
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="month" value="Bulan" />
                    <select id="month" name="month" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 dark:focus:border-teal-600 focus:ring-teal-500 dark:focus:ring-teal-600 rounded-xl shadow-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == now()->month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <x-input-label for="year" value="Tahun" />
                    <select id="year" name="year" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 dark:focus:border-teal-600 focus:ring-teal-500 dark:focus:ring-teal-600 rounded-xl shadow-sm">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    Batal
                </button>
                <button type="submit" @click="$dispatch('close')" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                    Ekspor Laporan PDF
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Script Chart -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Data dari Controller
            const chartData = @json($chartData);
            
            const dates = chartData.map(item => item.date);
            const totalVisits = chartData.map(item => item.count);
            const severeVisits = chartData.map(item => item.berat);

            const options = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: [
                    {
                        name: 'Total Kunjungan',
                        data: totalVisits
                    },
                    {
                        name: 'Kasus Berat / Darurat',
                        data: severeVisits
                    }
                ],
                colors: ['#0d9488', '#dc2626'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: dates,
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontWeight: 600,
                            fontSize: '11px'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontWeight: 600,
                            fontSize: '11px'
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: { show: true }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontWeight: 700,
                    fontSize: '12px',
                    markers: { radius: 12 }
                }
            };

            const chart = new ApexCharts(document.querySelector("#uksWeeklyChart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
