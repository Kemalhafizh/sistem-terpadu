<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Dasbor Alur Persetujuan') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Pemantauan antrean persetujuan berjenjang, beban peninjau, dan kepatuhan batas waktu respon') }}
                </p>
            </div>
        </div>
    </x-slot>

    @php
        $roleNames = [
            'admin' => __('Administrator'),
            'superadmin' => __('Super Administrator'),
            'kepala_sekolah' => __('Kepala Sekolah'),
            'waka_kurikulum' => __('Waka Kurikulum'),
            'waka_kesiswaan' => __('Waka Kesiswaan'),
            'waka_sarpras' => __('Waka Sarpras'),
            'waka_humas' => __('Waka Humas'),
            'staf_tu' => __('Staf Tata Usaha'),
            'bk' => __('Bimbingan Konseling (BK)'),
            'guru' => __('Guru'),
        ];
    @endphp

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Sub-navigasi Alur Kerja --}}
            <nav class="flex flex-wrap gap-1.5 p-1.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm" aria-label="{{ __('Navigasi Alur Kerja') }}">
                <a href="{{ route('workflows.dashboard') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors bg-primary-600 text-white">
                    {{ __('Ringkasan') }}
                </a>
                <a href="{{ route('workflows.pending') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/60">
                    {{ __('Menunggu Persetujuan') }}
                </a>
                <a href="{{ route('workflows.my-requests') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/60">
                    {{ __('Pengajuan Saya') }}
                </a>
                <a href="{{ route('workflows.definitions') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/60">
                    {{ __('Definisi Alur') }}
                </a>
                <a href="{{ route('workflows.history') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/60">
                    {{ __('Riwayat') }}
                </a>
                <a href="{{ route('workflows.analytics') }}" class="px-4 py-2 text-xs font-medium rounded-lg transition-colors text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700/60">
                    {{ __('Analitik') }}
                </a>
            </nav>

            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-800 dark:text-emerald-200 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-200 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Metrik Ringkasan --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Total Tertunda') }}</span>
                    <div class="text-3xl font-semibold text-slate-900 dark:text-white mt-3 tabular-nums">{{ $kpi['pending'] }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Disetujui') }}</span>
                    <div class="text-3xl font-semibold text-emerald-600 dark:text-emerald-400 mt-3 tabular-nums">{{ $kpi['approved'] }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Ditolak') }}</span>
                    <div class="text-3xl font-semibold text-rose-600 dark:text-rose-400 mt-3 tabular-nums">{{ $kpi['rejected'] }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Butuh Revisi') }}</span>
                    <div class="text-3xl font-semibold text-slate-900 dark:text-white mt-3 tabular-nums">{{ $kpi['revision'] }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-s-4 border-s-primary-600 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-primary-700 dark:text-primary-400">{{ __('Tugas Saya') }}</span>
                    <div class="text-3xl font-semibold text-primary-700 dark:text-primary-400 mt-3 tabular-nums">{{ $myPendingCount }}</div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 border-s-4 border-s-rose-600 rounded-xl p-5 flex flex-col justify-between">
                    <span class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ __('Melewati SLA') }}</span>
                    <div class="text-3xl font-semibold text-rose-600 dark:text-rose-400 mt-3 tabular-nums">{{ $overdueCount }}</div>
                </div>
            </div>

            {{-- Rata-rata Waktu Penyelesaian --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-start">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Kinerja Penyelesaian') }}</span>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mt-0.5">{{ __('Rata-rata Waktu Penyelesaian Alur Kerja') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-xl">
                        {{ __('Diukur berdasarkan durasi sejak pengajuan dibuat hingga keputusan akhir tercatat.') }}
                    </p>
                </div>
                <div class="flex items-baseline gap-2 px-5 py-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 shrink-0">
                    <span class="text-3xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ $avgResolutionTime }}</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('jam rata-rata') }}</span>
                </div>
            </div>

            {{-- Dua Kolom Operasional --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Antrean per Peran --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white leading-tight">{{ __('Sebaran Antrean per Jabatan') }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Jumlah dokumen yang sedang menunggu tindakan per peran peninjau') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($bottlenecks as $b)
                                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/70 dark:border-slate-700/70">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 text-sm">
                                            {{ $roleNames[$b->role] ?? Str::headline($b->role) }}
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 font-medium text-xs rounded-md border border-amber-200/70 dark:border-amber-800/60 tabular-nums">
                                        {{ $b->count }} {{ __('tertunda') }}
                                    </span>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 text-center text-slate-500 dark:text-slate-400 space-y-2">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-medium">{{ __('Tidak ada antrean tertunda untuk peran mana pun') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Daftar Melewati SLA --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900 dark:text-white leading-tight">{{ __('Pengajuan Melewati Batas Waktu (SLA)') }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Pengajuan yang memerlukan tindak lanjut segera') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @forelse($overdueRequests as $req)
                                <div class="p-3.5 bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/70 dark:border-slate-700/70 flex justify-between items-center gap-3">
                                    <div class="flex-1 min-w-0 pe-3">
                                        <h4 class="font-medium text-slate-900 dark:text-slate-100 text-sm truncate">
                                            {{ $req->definition?->name ?? __('Tanpa Nama Alur') }}
                                        </h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                            <span>{{ __('Pengaju') }}: <strong class="font-medium text-slate-700 dark:text-slate-300">{{ $req->requester?->name ?? '-' }}</strong></span>
                                            <span>&middot;</span>
                                            <span class="text-rose-600 dark:text-rose-400 font-medium inline-flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span>{{ __('Jatuh tempo') }} {{ \Carbon\Carbon::parse($req->due_at)->diffForHumans() }}</span>
                                            </span>
                                        </div>
                                    </div>
                                    <a href="{{ route('workflows.show', $req) }}" class="inline-flex items-center px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg transition-colors shrink-0">
                                        {{ __('Detail') }}
                                    </a>
                                </div>
                            @empty
                                <div class="flex flex-col items-center justify-center py-10 text-center text-slate-500 dark:text-slate-400 space-y-2">
                                    <svg class="w-10 h-10 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-sm font-medium">{{ __('Tidak ada pengajuan yang melewati batas waktu SLA') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
