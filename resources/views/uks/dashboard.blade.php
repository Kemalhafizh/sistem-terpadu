<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-white leading-tight">
                    {{ __('UKS Digital (Unit Kesehatan Sekolah)') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ school_short_name() }} &middot; {{ __('Pelayanan Kesehatan & Rekam Medis Siswa') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('uks.visits.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('Catat Kunjungan Baru') }}</span>
                </a>

                <button type="button" @click="$dispatch('open-modal', 'export-monthly-modal')" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>{{ __('Laporan PDF Bulanan') }}</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="rounded-xl p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 text-emerald-800 dark:text-emerald-200">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Statistik Utama --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Kunjungan Hari Ini') }}</p>
                    <h3 class="text-3xl font-semibold text-slate-900 dark:text-white mt-2 tabular-nums">{{ $todayVisitsCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Siswa tercatat masuk UKS') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 border-s-4 border-s-rose-600">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Kasus Berat Hari Ini') }}</p>
                    <h3 class="text-3xl font-semibold text-rose-600 dark:text-rose-400 mt-2 tabular-nums">{{ $criticalCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Memerlukan penanganan segera') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Stok Obat Menipis') }}</p>
                    <h3 class="text-3xl font-semibold text-amber-600 dark:text-amber-400 mt-2 tabular-nums">{{ $lowStockCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Persediaan di bawah batas minimum') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Kedaluwarsa Terdekat') }}</p>
                    <h3 class="text-3xl font-semibold text-amber-600 dark:text-amber-400 mt-2 tabular-nums">{{ $expiringSoonCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Dalam 30 hari ke depan') }}</p>
                </div>
            </div>

            {{-- Navigasi Cepat Layanan UKS --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('uks.health-records.index') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary-500/60 dark:hover:border-primary-500/60 rounded-xl p-5 shadow-sm transition-colors flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-sm text-slate-900 dark:text-white">{{ __('Rekam Medis Siswa') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Alergi, kondisi kronis, dan riwayat medis') }}</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </a>

                <a href="{{ route('uks.screenings.index') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary-500/60 dark:hover:border-primary-500/60 rounded-xl p-5 shadow-sm transition-colors flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-sm text-slate-900 dark:text-white">{{ __('Penjaringan Kesehatan') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Pemeriksaan berkala tinggi, berat, dan indera') }}</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </a>

                <a href="{{ route('uks.medicines.dispense-log') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary-500/60 dark:hover:border-primary-500/60 rounded-xl p-5 shadow-sm transition-colors flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-sm text-slate-900 dark:text-white">{{ __('Catatan Pemberian Obat') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Riwayat pemakaian obat pada kunjungan siswa') }}</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </a>

                <a href="{{ route('uks.medicines.restock-log') }}" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-primary-500/60 dark:hover:border-primary-500/60 rounded-xl p-5 shadow-sm transition-colors flex items-start justify-between gap-3">
                    <div>
                        <h4 class="font-semibold text-sm text-slate-900 dark:text-white">{{ __('Catatan Penambahan Stok') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Penerimaan dan pembaruan persediaan obat UKS') }}</p>
                    </div>
                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
            </div>

            {{-- Grafik Tren Mingguan --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Tren & Keparahan Kunjungan Medis') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Evaluasi kunjungan 7 hari terakhir') }}</p>
                    </div>
                </div>
                <div id="uksWeeklyChart" class="w-full h-80"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Kunjungan Terkini --}}
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col">
                    <div class="flex justify-between items-center mb-5">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Kunjungan Terkini') }}</h4>
                        <a href="{{ route('uks.visits.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            <span>{{ __('Lihat semua') }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>

                    <div class="flex-1 space-y-3">
                        @forelse($recentVisits as $visit)
                            @php
                                $dispLabels = [
                                    'kembali_kelas' => ['bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-800/60', __('Kembali ke Kelas')],
                                    'istirahat_uks' => ['bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600', __('Istirahat UKS')],
                                    'pulang' => ['bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200/70 dark:border-sky-800/60', __('Pulang')],
                                    'rujuk_rs' => ['bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200/70 dark:border-rose-800/60', __('Rujuk RS')],
                                ];
                                $disp = $dispLabels[$visit->disposition] ?? ['bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600', __('Istirahat')];
                            @endphp
                            <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg flex items-center justify-center shrink-0 font-semibold text-sm">
                                        {{ mb_substr($visit->student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-medium text-sm text-slate-900 dark:text-white">{{ $visit->student->user->name }}</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">({{ $visit->student->classroom->name ?? __('Tanpa Rombel') }})</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ __('Keluhan') }}: <span class="font-medium text-slate-900 dark:text-white">{{ $visit->complaint }}</span></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 tabular-nums">
                                            {{ $visit->visit_time->translatedFormat('d M Y, H:i') }} WIB &middot; {{ __('Petugas') }}: {{ $visit->handler->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                    @if($visit->severity === 'berat')
                                        <span class="px-2.5 py-0.5 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-800/60 rounded-md text-xs font-medium">{{ __('Berat') }}</span>
                                    @elseif($visit->severity === 'sedang')
                                        <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/70 dark:border-amber-800/60 rounded-md text-xs font-medium">{{ __('Sedang') }}</span>
                                    @else
                                        <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-800/60 rounded-md text-xs font-medium">{{ __('Ringan') }}</span>
                                    @endif

                                    <span class="px-2.5 py-0.5 {{ $disp[0] }} border rounded-md text-xs font-medium">{{ $disp[1] }}</span>

                                    <a href="{{ route('uks.visits.show', $visit->uuid) }}" class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" title="{{ __('Detail') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-sm font-medium">{{ __('Belum ada kunjungan siswa hari ini') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Stok Kritis & Kedaluwarsa --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col">
                    <div class="flex justify-between items-center mb-5">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Stok Kritis & Kedaluwarsa') }}</h4>
                        <a href="{{ route('uks.medicines.index') }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                            <span>{{ __('Inventaris') }}</span>
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>

                    <div class="flex-grow space-y-3">
                        @forelse($lowStockMedicines as $med)
                            @php
                                $stockPct = $med->stock > 0 ? min(100, max(0, ($med->stock / max(1, $med->min_stock * 2)) * 100)) : 0;
                                $colorClass = $med->stock <= $med->min_stock ? 'bg-rose-500' : 'bg-amber-500';
                            @endphp
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-xl space-y-2 tabular-nums">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h5 class="text-sm font-medium text-slate-900 dark:text-white leading-tight">{{ $med->name }}</h5>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $med->category }}</p>
                                    </div>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/50">
                                        {{ __('Stok') }}: {{ $med->stock }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full rounded-full {{ $colorClass }}" style="width: {{ $stockPct }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ __('Minimum') }}: {{ $med->min_stock }}</span>
                                    @if($med->expiry_date)
                                        <span class="{{ $med->expiry_date->isPast() ? 'text-rose-600 dark:text-rose-400 font-medium' : ($med->expiry_date->diffInDays(now()) < 30 ? 'text-amber-600 dark:text-amber-400 font-medium' : '') }}">
                                            {{ __('Kedaluwarsa') }}: {{ $med->expiry_date->translatedFormat('d/m/y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs font-medium">{{ __('Seluruh persediaan obat berada pada batas aman') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal name="export-monthly-modal" focusable>
        <form method="GET" action="{{ route('uks.export.monthly') }}" class="p-6">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Ekspor Laporan Bulanan UKS') }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Pilih periode bulan untuk mengunduh laporan PDF resmi.') }}</p>

            <div class="mt-5 grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="month" :value="__('Bulan')" />
                    <select id="month" name="month" class="mt-1 block w-full border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" @selected($m == now()->month)>
                                {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="year" :value="__('Tahun')" />
                    <select id="year" name="year" class="mt-1 block w-full border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 focus:border-primary-500 focus:ring-primary-500 rounded-lg shadow-sm text-sm tabular-nums">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2.5">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-medium transition-colors">
                    {{ __('Batal') }}
                </button>
                <button type="submit" @click="$dispatch('close')" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors">
                    {{ __('Ekspor Laporan PDF') }}
                </button>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chartData = @json($chartData);
            const isDark = document.documentElement.classList.contains('dark');
            const labelColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            new ApexCharts(document.querySelector("#uksWeeklyChart"), {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                series: [
                    { name: @js(__('Total Kunjungan')), data: chartData.map(item => item.count) },
                    { name: @js(__('Kasus Berat')), data: chartData.map(item => item.berat) }
                ],
                colors: ['#0d9488', '#e11d48'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02, stops: [0, 90, 100] }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                xaxis: {
                    categories: chartData.map(item => item.date),
                    labels: { style: { colors: labelColor, fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: labelColor, fontSize: '11px' } }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '12px',
                    labels: { colors: labelColor }
                }
            }).render();
        });
    </script>
</x-app-layout>
