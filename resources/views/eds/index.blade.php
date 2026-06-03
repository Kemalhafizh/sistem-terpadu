<x-app-layout>
    <x-slot name="title">Evaluasi Diri Sekolah (EDS)</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span>🛡️</span> Raport Mutu Sekolah (EDS)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem penjaminan mutu internal terintegrasi 8 Standar Nasional Pendidikan (SNP) untuk persiapan akreditasi BAN-S/M.</p>
            </div>
            @if(auth()->user()->role !== 'kepala_sekolah')
                @can('manage', App\Models\EdsPeriod::class)
                <button @click="$dispatch('open-modal', 'initiate-eds-modal')" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                    Inisiasi EDS Baru
                </button>
                @endcan
            @endif
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ activeTab: 'all' }">
        <!-- Alert Sukses/Gagal -->
        @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50" role="alert">
            <span class="font-bold">Berhasil!</span> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50" role="alert">
            <span class="font-bold">Error!</span> {{ session('error') }}
        </div>
        @endif

        <!-- Panel Ringkasan KPI (Glassmorphic Cards) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Evaluasi</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $periods->count() }}</h3>
                </div>
            </div>
            
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl text-amber-600 dark:text-amber-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sedang Berjalan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $periods->where('status', 'in_progress')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/50 rounded-xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Telah Diselesaikan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $periods->where('status', 'completed')->count() }}</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tahun Akademik</p>
                    <h3 class="text-sm font-extrabold text-gray-800 dark:text-white mt-1.5">{{ $activeYear ? $activeYear->year . ' (' . ucfirst($activeYear->semester) . ')' : 'Tidak Aktif' }}</h3>
                </div>
            </div>
        </div>

        <!-- Filter & Header Tab -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex space-x-2 bg-gray-100 dark:bg-gray-900 p-1 rounded-xl w-full sm:w-auto">
                <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white'" class="flex-1 sm:flex-none px-4 py-2 text-xs font-black uppercase tracking-wider rounded-lg transition-all">Semua</button>
                <button @click="activeTab = 'in_progress'" :class="activeTab === 'in_progress' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white'" class="flex-1 sm:flex-none px-4 py-2 text-xs font-black uppercase tracking-wider rounded-lg transition-all">Berjalan</button>
                <button @click="activeTab = 'completed'" :class="activeTab === 'completed' ? 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-800 dark:hover:text-white'" class="flex-1 sm:flex-none px-4 py-2 text-xs font-black uppercase tracking-wider rounded-lg transition-all">Selesai</button>
            </div>
            
            <a href="{{ route('eds.comparison') }}" class="w-full sm:w-auto inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 px-4 py-2 rounded-xl text-xs font-bold transition-all border border-gray-200/50 dark:border-gray-600/50">
                <svg class="w-4 h-4 mr-1.5 text-gray-550" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Bandingkan 2 Periode
            </a>
        </div>

        <!-- Grid Periode EDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($periods as $period)
            <div x-show="activeTab === 'all' || activeTab === '{{ $period->status }}'" class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden flex flex-col justify-between transform hover:scale-[1.01] transition-all duration-300">
                
                <!-- Card Header -->
                <div class="p-6 pb-4">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-black tracking-widest text-primary-500 uppercase">{{ $period->academicYear->year }} ({{ ucfirst($period->academicYear->semester) }})</span>
                        @if($period->status === 'draft')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-gray-150 text-gray-700 dark:bg-gray-900 dark:text-gray-400 border border-gray-200 dark:border-gray-750">DRAFT</span>
                        @elseif($period->status === 'in_progress')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">BERJALAN</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">SELESAI</span>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mt-2 leading-snug hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        <a href="{{ route('eds.show', $period->id) }}">{{ $period->name }}</a>
                    </h3>

                    <!-- Tanggal Periode -->
                    <div class="flex items-center space-x-2 text-xs text-gray-400 dark:text-gray-500 mt-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>
                            {{ $period->start_date ? \Carbon\Carbon::parse($period->start_date)->format('d M Y') : '-' }} s/d {{ $period->end_date ? \Carbon\Carbon::parse($period->end_date)->format('d M Y') : '-' }}
                        </span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6 space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-gray-450 dark:text-gray-400">Progres Evaluasi</span>
                            <span class="font-extrabold text-gray-900 dark:text-white">{{ $period->overall_progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-900 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary-500 to-indigo-500 transition-all duration-500 rounded-full" style="width: {{ $period->overall_progress }}%"></div>
                        </div>
                        <div class="text-[10px] text-gray-400 flex justify-between">
                            <span>{{ $period->assessed_indicators }} dari {{ $period->total_indicators }} indikator dinilai</span>
                            @if($period->overall_progress == 100)
                                <span class="text-emerald-500 font-bold flex items-center gap-0.5">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Lengkap
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Footer / Skor Agregat -->
                <div class="px-6 py-4 bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-150/50 dark:border-gray-750/50 flex justify-between items-center">
                    <div>
                        <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Rata-rata Skor</div>
                        <div class="flex items-baseline space-x-1 mt-0.5">
                            <span class="text-xl font-extrabold text-gray-900 dark:text-white">{{ number_format($period->overall_average, 2) }}</span>
                            <span class="text-xs text-gray-400">/ 4.00</span>
                        </div>
                    </div>
                    
                    @php
                        $roundedScore = round($period->overall_average);
                        $badgeClass = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
                        $predikat = 'Belum Dinilai';
                        
                        if ($period->assessed_indicators > 0) {
                            if ($period->overall_average >= 3.5) {
                                $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50';
                                $predikat = 'Unggul';
                            } elseif ($period->overall_average >= 3.0) {
                                $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-250 dark:border-blue-900/50';
                                $predikat = 'Baik';
                            } elseif ($period->overall_average >= 2.0) {
                                $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-250 dark:border-amber-900/50';
                                $predikat = 'Cukup';
                            } else {
                                $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-400 border border-red-250 dark:border-red-900/50';
                                $predikat = 'Kurang';
                            }
                        }
                    @endphp
                    
                    <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-black uppercase {{ $badgeClass }}">{{ $predikat }}</span>
                </div>
                
                <!-- Action Button -->
                <div class="px-6 pb-6 bg-gray-50/50 dark:bg-gray-900/30">
                    <a href="{{ route('eds.show', $period->id) }}" class="w-full inline-flex justify-center items-center bg-gray-900 dark:bg-white text-white dark:text-gray-950 hover:bg-black dark:hover:bg-gray-50 rounded-xl py-2.5 text-xs font-bold tracking-wide transition-all shadow-md">
                        Buka Dashboard
                        <svg class="w-4 h-4 ml-1.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-12 text-center rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
                <span class="text-5xl">📊</span>
                <h3 class="text-lg font-black text-gray-800 dark:text-white mt-4">Belum Ada Periode EDS</h3>
                <p class="text-sm text-gray-400 dark:text-gray-550 mt-1 max-w-md mx-auto">Silakan lakukan inisiasi periode Evaluasi Diri Sekolah baru untuk memulai penilaian mutu sekolah Anda.</p>
                @can('manage', App\Models\EdsPeriod::class)
                <button @click="$dispatch('open-modal', 'initiate-eds-modal')" class="mt-6 inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-6 text-sm font-black uppercase tracking-wider transition-all">
                    Inisiasi EDS Pertama
                </button>
                @endcan
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Inisiasi EDS Baru -->
    <x-modal name="initiate-eds-modal" focusable>
        <form method="post" action="{{ route('eds.store') }}" class="p-6 bg-white dark:bg-gray-900 rounded-3xl">
            @csrf
            
            <h2 class="text-lg font-black text-gray-950 dark:text-white flex items-center gap-1.5">
                <span>🛡️</span> Inisiasi Periode EDS Baru
            </h2>
            
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                Membuat periode evaluasi mutu baru. Sistem akan secara otomatis menautkan periode ini dengan Tahun Akademik aktif saat ini (<strong>{{ $activeYear ? $activeYear->year . ' - ' . ucfirst($activeYear->semester) : 'TIDAK ADA' }}</strong>).
            </p>

            @if(!$activeYear)
            <div class="mt-4 p-4 text-xs text-red-800 rounded-xl bg-red-50 dark:bg-red-950/20 dark:text-red-400 border border-red-200 dark:border-red-900/30">
                <strong>Peringatan!</strong> Tahun akademik aktif belum didefinisikan. Anda tidak dapat membuat periode EDS sebelum mengaktifkan Tahun Akademik.
            </div>
            @endif

            <div class="mt-6 space-y-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama/Judul Periode</label>
                    <input 
                        id="name" 
                        name="name" 
                        type="text" 
                        required 
                        placeholder="Contoh: Evaluasi Diri Sekolah 2026 Ganjil" 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                        <input 
                            id="start_date" 
                            name="start_date" 
                            type="date" 
                            class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tanggal Selesai (Target)</label>
                        <input 
                            id="end_date" 
                            name="end_date" 
                            type="date" 
                            class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <button type="button" @click="$dispatch('close')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                    Batal
                </button>
                <button type="submit" @if(!$activeYear) disabled @endif class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all">
                    Mulai Inisiasi
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
