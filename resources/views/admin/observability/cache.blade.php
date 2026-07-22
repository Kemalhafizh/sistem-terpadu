<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    Multi-Layer Distributed Caching & Tag Invalidation Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pengelolaan strategi caching 4-layer (In-Memory, Shared Store, Response ETag, & Invalidation Tags).</p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('admin.observability.cache.warmup') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center gap-2 transition-all">
                        ⚡ Warmup Cache System
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Driver Cache Aktif</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">{{ $metrics['driver'] }}</h4>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl font-black text-xs">SHARED</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Dukungan Cache Tags</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1">
                            {{ $metrics['supports_tags'] ? 'Dukungan Penuh' : 'Fallback Prefix Mode' }}
                        </h4>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-2xl font-black text-xs">TAGS</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Status Cache Layer</span>
                        <h4 class="text-xl font-black text-emerald-500 mt-1 font-mono">ONLINE (OPERATIONAL)</h4>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl font-black text-xs">READY</div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Response Gateway ETag</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">304 NOT MODIFIED</h4>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl font-black text-xs">ETAG</div>
                </div>
            </div>

            <!-- Architecture Layer Explanation Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h3 class="text-xs font-black uppercase text-emerald-600 dark:text-emerald-400 tracking-wider">Layer 1: In-Memory Request Lifecycle Cache</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        Singleton service <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">RequestLifetimeCacheService</code> menyimpan query model dan konfigurasi sementara dalam memori RAM PHP selama 1x HTTP request lifecycle tanpa memicu I/O disk ganda.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h3 class="text-xs font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-wider">Layer 2: Shared Key-Value Cache Engine</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        Menggudangkan data konstan (Profil Sekolah, Mata Pelajaran, Tahun Ajaran, Daftar Ruangan, Peran User) dengan TTL 3600s untuk menekan beban database hingga 85%.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h3 class="text-xs font-black uppercase text-amber-600 dark:text-amber-400 tracking-wider">Layer 3: HTTP Response Cache Gateway (ETag)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        Middleware <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">HttpCacheHeadersMiddleware</code> secara otomatis menghitung SHA-1 ETag dan mengembalikan status HTTP 304 Not Modified jika halaman publik belum berubah.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h3 class="text-xs font-black uppercase text-rose-600 dark:text-rose-400 tracking-wider">Layer 4: Targeted Cache Tag Invalidation</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                        Manajemen pembersihan cache terisolasi berbasis Tag (<code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">academic</code>, <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">finance</code>, <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-900 px-1.5 py-0.5 rounded">school</code>) tanpa perlu membuang cache modul lainnya saat terjadi mutasi data.
                    </p>
                </div>
            </div>

            <!-- Targeted Tag Purge Control Panel -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Pembersihan Cache Terarah (Targeted Tag Purging)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih grup tag tertentu untuk dibersihkan tanpa mengganggu memori cache domain lain.</p>
                    </div>

                    <form action="{{ route('admin.observability.cache.flush-all') }}" method="POST" onsubmit="return confirm('Peringatan! Anda akan menghapus SELURUH Cache aplikasi. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                            Purge All Application Cache
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <form action="{{ route('admin.observability.cache.flush-tag') }}" method="POST" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                        @csrf
                        <input type="hidden" name="tag" value="academic">
                        <strong class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider block">Domain Akademik</strong>
                        <p class="text-[10px] text-gray-400">Cache Nilai, CBT, & Rapor</p>
                        <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs transition-all">
                            Purge Academic Cache
                        </button>
                    </form>

                    <form action="{{ route('admin.observability.cache.flush-tag') }}" method="POST" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                        @csrf
                        <input type="hidden" name="tag" value="finance">
                        <strong class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider block">Domain Keuangan</strong>
                        <p class="text-[10px] text-gray-400">Cache Saldo SPP & Wadiah</p>
                        <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition-all">
                            Purge Finance Cache
                        </button>
                    </form>

                    <form action="{{ route('admin.observability.cache.flush-tag') }}" method="POST" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                        @csrf
                        <input type="hidden" name="tag" value="keislaman">
                        <strong class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider block">Domain Keislaman</strong>
                        <p class="text-[10px] text-gray-400">Cache Tahfidz & Mutaba'ah</p>
                        <button type="submit" class="w-full py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs transition-all">
                            Purge Keislaman Cache
                        </button>
                    </form>

                    <form action="{{ route('admin.observability.cache.flush-tag') }}" method="POST" class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-3">
                        @csrf
                        <input type="hidden" name="tag" value="school">
                        <strong class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider block">Domain Sekolah</strong>
                        <p class="text-[10px] text-gray-400">Cache Profil & Kop Surat</p>
                        <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-xs transition-all">
                            Purge School Cache
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
