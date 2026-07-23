<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-650 dark:text-teal-400">Integrasi Data Eksternal</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight mt-1">
                    Dapodik & EMIS Sync Mapper
                </h2>
            </div>
            <a href="{{ route('admin.integration.sync.history') }}" 
               class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-200 active:scale-95 shadow-lg shadow-teal-600/25 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Sinkronisasi</span>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
            
            {{-- Notification Banners --}}
            @if(session('success'))
                <div class="bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-500/30 rounded-3xl p-5 shadow-sm flex items-center gap-4 animate-fade-in-up">
                    <div class="w-10 h-10 bg-emerald-500/20 border border-emerald-500/40 rounded-2xl flex items-center justify-center text-emerald-600 shrink-0">
                        
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-gradient-to-r from-rose-500/10 to-red-500/10 border border-rose-500/30 rounded-3xl p-5 shadow-sm flex items-center gap-4 animate-fade-in-up">
                    <div class="w-10 h-10 bg-rose-500/20 border border-rose-500/40 rounded-2xl flex items-center justify-center text-rose-600 shrink-0">
                        
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Audit Issues Summary Indicator Block --}}
            @if($totalIssuesCount > 0)
                <div class="bg-gradient-to-r from-rose-500/10 via-amber-500/10 to-rose-500/10 border-2 border-rose-500/30 rounded-[2.5rem] p-8 shadow-xl relative overflow-hidden group">
                    <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-rose-500/20 rounded-full blur-3xl animate-pulse"></div>
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 bg-rose-500/20 border border-rose-500/45 rounded-3xl flex items-center justify-center text-3xl animate-bounce shrink-0 shadow-lg shadow-rose-500/20">
                                
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-rose-600 dark:text-rose-400">Peringatan Integritas Data</span>
                                <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Ditemukan {{ $totalIssuesCount }} Masalah Data</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 max-w-2xl">
                                    Beberapa data siswa atau guru memiliki ketidaksesuaian dengan format validasi kementerian. Direkomendasikan untuk melakukan audit dan perbaikan data sebelum melakukan ekspor sinkronisasi.
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3 shrink-0">
                            <span class="px-4 py-2 bg-white dark:bg-gray-800 border border-rose-200 dark:border-rose-900/50 rounded-2xl text-xs font-bold text-rose-600 dark:text-rose-455 flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                Dapodik: {{ $dapodikStudentIssuesCount + $dapodikTeacherIssuesCount }} Masalah
                            </span>
                            <span class="px-4 py-2 bg-white dark:bg-gray-800 border border-amber-200 dark:border-amber-900/50 rounded-2xl text-xs font-bold text-amber-600 dark:text-amber-455 flex items-center gap-1.5 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                EMIS: {{ $emisStudentIssuesCount + $emisTeacherIssuesCount }} Masalah
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/10 border border-emerald-500/30 rounded-[2.5rem] p-8 shadow-md relative overflow-hidden">
                    <div class="relative z-10 flex items-center gap-5">
                        <div class="w-16 h-16 bg-emerald-500/20 border border-emerald-500/40 rounded-3xl flex items-center justify-center text-3xl shrink-0 shadow-lg shadow-emerald-500/20">
                            
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">Status Integritas Data</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Data Bersih & Sinkron</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                Semua data lokal siswa dan guru valid dan siap untuk disinkronisasi ke Dapodik & EMIS.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Two Main Integration Cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Dapodik Integration Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <!-- Accent top line -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-blue-500 to-teal-500"></div>
                    
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">Kementerian Pendidikan, Kebudayaan, Riset, dan Teknologi</span>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mt-1">Dapodik Integration</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sinkronisasi data siswa & PTK dengan sistem Data Pokok Pendidikan.</p>
                            </div>
                            <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shrink-0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif
                            </span>
                        </div>

                        <div class="space-y-6 mt-8">
                            <!-- Siswa Section -->
                            <div class="bg-slate-50 dark:bg-gray-900/40 border border-slate-100 dark:border-gray-800 rounded-3xl p-6 hover:border-blue-500/30 transition-all duration-200">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg font-bold">
                                            
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white">Data Siswa</h4>
                                            <p class="text-xs text-gray-400">Target: students</p>
                                        </div>
                                    </div>
                                    @if($dapodikStudentIssuesCount > 0)
                                        <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-rose-200 dark:border-rose-900/30">
                                            {{ $dapodikStudentIssuesCount }} Isu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200 dark:border-emerald-900/30">
                                            Valid
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <a href="{{ route('admin.integration.sync.mapping', ['dapodik', 'students']) }}" class="flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-500 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 hover:bg-blue-50/20 shadow-sm">
                                        Kelola Pemetaan
                                    </a>
                                    <a href="{{ route('admin.integration.sync.validation', ['dapodik', 'students']) }}" class="flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 shadow-md shadow-blue-600/10">
                                        Mulai Audit
                                    </a>
                                </div>
                            </div>

                            <!-- Guru Section -->
                            <div class="bg-slate-50 dark:bg-gray-900/40 border border-slate-100 dark:border-gray-800 rounded-3xl p-6 hover:border-teal-500/30 transition-all duration-200">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-teal-100 dark:bg-teal-900/40 rounded-2xl flex items-center justify-center text-teal-600 dark:text-teal-400 text-lg font-bold">
                                            
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white">Data Guru / PTK</h4>
                                            <p class="text-xs text-gray-400">Target: teachers</p>
                                        </div>
                                    </div>
                                    @if($dapodikTeacherIssuesCount > 0)
                                        <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-rose-200 dark:border-rose-900/30">
                                            {{ $dapodikTeacherIssuesCount }} Isu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200 dark:border-emerald-900/30">
                                            Valid
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <a href="{{ route('admin.integration.sync.mapping', ['dapodik', 'teachers']) }}" class="flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-teal-500 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 hover:bg-teal-50/20 shadow-sm">
                                        Kelola Pemetaan
                                    </a>
                                    <a href="{{ route('admin.integration.sync.validation', ['dapodik', 'teachers']) }}" class="flex items-center justify-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 shadow-md shadow-teal-600/10">
                                        Mulai Audit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMIS Integration Card -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <!-- Accent top line -->
                    <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-emerald-500 to-cyan-500"></div>
                    
                    <div>
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-bold font-black">Kementerian Agama RI</span>
                                <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight mt-1">EMIS Integration</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sinkronisasi data siswa & PTK dengan Education Management Information System.</p>
                            </div>
                            <span class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shrink-0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Aktif
                            </span>
                        </div>

                        <div class="space-y-6 mt-8">
                            <!-- Siswa Section -->
                            <div class="bg-slate-50 dark:bg-gray-900/40 border border-slate-100 dark:border-gray-800 rounded-3xl p-6 hover:border-emerald-500/30 transition-all duration-200">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg font-bold">
                                            
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white">Data Siswa</h4>
                                            <p class="text-xs text-gray-400">Target: students</p>
                                        </div>
                                    </div>
                                    @if($emisStudentIssuesCount > 0)
                                        <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-rose-200 dark:border-rose-900/30">
                                            {{ $emisStudentIssuesCount }} Isu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200 dark:border-emerald-900/30">
                                            Valid
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <a href="{{ route('admin.integration.sync.mapping', ['emis', 'students']) }}" class="flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-emerald-500 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 hover:bg-emerald-50/20 shadow-sm">
                                        Kelola Pemetaan
                                    </a>
                                    <a href="{{ route('admin.integration.sync.validation', ['emis', 'students']) }}" class="flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 shadow-md shadow-emerald-600/10">
                                        Mulai Audit
                                    </a>
                                </div>
                            </div>

                            <!-- Guru Section -->
                            <div class="bg-slate-50 dark:bg-gray-900/40 border border-slate-100 dark:border-gray-800 rounded-3xl p-6 hover:border-cyan-500/30 transition-all duration-200">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-cyan-100 dark:bg-cyan-900/40 rounded-2xl flex items-center justify-center text-cyan-600 dark:text-cyan-400 text-lg font-bold">
                                            
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 dark:text-white">Data Guru / PTK</h4>
                                            <p class="text-xs text-gray-400">Target: teachers</p>
                                        </div>
                                    </div>
                                    @if($emisTeacherIssuesCount > 0)
                                        <span class="px-2.5 py-1 bg-rose-100 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-rose-200 dark:border-rose-900/30">
                                            {{ $emisTeacherIssuesCount }} Isu
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-wider rounded-lg border border-emerald-200 dark:border-emerald-900/30">
                                            Valid
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 gap-3 mt-4">
                                    <a href="{{ route('admin.integration.sync.mapping', ['emis', 'teachers']) }}" class="flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-cyan-500 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 hover:bg-cyan-50/20 shadow-sm">
                                        Kelola Pemetaan
                                    </a>
                                    <a href="{{ route('admin.integration.sync.validation', ['emis', 'teachers']) }}" class="flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-150 shadow-md shadow-emerald-600/10">
                                        Mulai Audit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 10 Recent Sync Reports --}}
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Log Aktivitas</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">10 Aktivitas Sinkronisasi Terakhir</h3>
                    </div>
                    <a href="{{ route('admin.integration.sync.history') }}" class="text-xs font-black uppercase tracking-widest text-teal-655 dark:text-teal-400 hover:text-teal-700 dark:hover:text-teal-300 flex items-center gap-1">
                        <span>Riwayat Selengkapnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="overflow-x-auto -mx-8 sm:-mx-8">
                    <div class="inline-block min-w-full align-middle px-8">
                        <div class="overflow-hidden border border-gray-100 dark:border-gray-700 rounded-3xl">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Sistem Target</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Tabel Lokal</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Tanggal Run</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Total Data</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Sukses</th>
                                        <th scope="col" class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Gagal</th>
                                        <th scope="col" class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-gray-405 dark:text-gray-500">Operator</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @forelse($reports as $report)
                                        <tr class="hover:bg-slate-50/55 dark:hover:bg-gray-950/20 transition-all duration-100">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($report->system_target === 'dapodik')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-100 dark:border-blue-900/30">
                                                        Dapodik
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/30">
                                                        EMIS
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($report->local_table === 'students')
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-100 dark:border-purple-900/30">
                                                        Siswa
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-100 dark:border-amber-900/30">
                                                        Guru
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                {{ $report->sync_run_at ? $report->sync_run_at->timezone('Asia/Jakarta')->format('d M Y H:i') : $report->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                                <span class="text-[10px] text-gray-400 block mt-0.5">{{ $report->created_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-bold text-gray-900 dark:text-white">
                                                {{ $report->records_processed }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-450 bg-emerald-50 dark:bg-emerald-950/20 px-2.5 py-1 rounded-lg">
                                                    {{ $report->records_success }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($report->records_failed > 0)
                                                    <span class="text-xs font-bold text-rose-650 dark:text-rose-455 bg-rose-50 dark:bg-rose-950/20 px-2.5 py-1 rounded-lg animate-pulse">
                                                        {{ $report->records_failed }}
                                                    </span>
                                                @else
                                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-650 px-2.5 py-1">
                                                        0
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-300 font-semibold">
                                                {{ $report->runBy->name ?? 'System' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-xs text-gray-400 dark:text-gray-500 italic">
                                                Belum ada riwayat aktivitas sinkronisasi.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
