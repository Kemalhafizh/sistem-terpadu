<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Papan Skor Kesehatan Keuangan (Financial Health Scoreboard)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Dihitung dari transaksi yang tercatat di sistem. Angka tanpa sumber data ditampilkan sebagai "Belum ada data".
                </p>
            </div>
        </div>
    </x-slot>

    @php
        // Every caption below is derived from the numbers themselves — these
        // used to be fixed verdicts ("Likuiditas Kuat", "Target > 90% Tercapai")
        // shown whatever the figures were.
        $details = $metric?->details ?? [];
        $rupiah = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
        $tanggal = fn ($d) => $d ? \Carbon\Carbon::parse($d)->translatedFormat('d M Y') : null;
        $runway = $details['runway_months'] ?? null;
        $collection = $metric?->fee_collection_rate;
        $burn = $details['burn_rate'] ?? [];
        $projected = $details['projected_revenue'] ?? [];
        $cashInfo = $details['cash_reserve'] ?? [];
        $feeInfo = $details['fee_collection'] ?? [];
        $labels = \App\Services\Dashboard\FinancialScoreboardService::EXPENSE_LABELS;
    @endphp

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @if($metric)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Cash --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Saldo Rekening Terdaftar</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        {{ $metric->cash_reserve !== null ? $rupiah($metric->cash_reserve) : 'Belum ada data' }}
                    </div>
                    @if($metric->cash_reserve === null)
                        <p class="text-xs text-slate-500">Belum ada rekening bank aktif yang didaftarkan bendahara.</p>
                    @else
                        <p class="text-xs text-slate-500">
                            {{ $cashInfo['accounts'] ?? 0 }} rekening aktif, diinput manual
                            @if(!empty($cashInfo['as_of'])) — terakhir diperbarui {{ $tanggal($cashInfo['as_of']) }} @endif
                        </p>
                        @if($runway !== null)
                            <p class="text-xs font-semibold {{ $runway < 3 ? 'text-rose-600' : ($runway < 6 ? 'text-amber-600' : 'text-emerald-600') }}">
                                Cukup untuk ± {{ $runway }} bulan pengeluaran
                            </p>
                        @endif
                    @endif
                </div>

                {{-- Burn rate --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Rata-rata Pengeluaran Bulanan</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        {{ $metric->monthly_burn_rate !== null ? $rupiah($metric->monthly_burn_rate) : 'Belum ada data' }}
                    </div>
                    <p class="text-xs text-slate-500">
                        @if(!empty($burn['period_start']))
                            {{ $burn['months'] ?? 3 }} bulan penuh terakhir ({{ $tanggal($burn['period_start']) }} – {{ $tanggal($burn['period_end']) }}):
                            payroll dibayar, vendor dibayar, kas kecil disetujui.
                        @endif
                        @if($metric->monthly_burn_rate === null)
                            Belum ada pengeluaran tercatat pada periode ini.
                        @endif
                    </p>
                </div>

                {{-- Projected revenue --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Tagihan Jatuh Tempo Bulan Depan</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        {{ $metric->projected_revenue !== null ? $rupiah($metric->projected_revenue) : 'Belum ada data' }}
                    </div>
                    <p class="text-xs text-slate-500">
                        @if($metric->projected_revenue === null)
                            Tagihan bulan {{ $projected['month'] ?? 'depan' }} belum diterbitkan.
                        @else
                            Sisa belum dibayar dari {{ $projected['billings_due'] ?? 0 }} tagihan ({{ $projected['month'] ?? '' }}).
                        @endif
                        Diterima bulan ini: {{ $rupiah($details['collected_this_month'] ?? 0) }}.
                    </p>
                </div>

                {{-- Collection rate --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Tingkat Koleksi SPP</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">
                        {{ $collection !== null ? rtrim(rtrim(number_format((float) $collection, 2, ',', '.'), '0'), ',').'%' : 'Belum ada data' }}
                    </div>
                    @if($collection === null)
                        <p class="text-xs text-slate-500">Belum ada tagihan yang diterbitkan.</p>
                    @else
                        <p class="text-xs font-semibold {{ $collection >= 90 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $collection >= 90 ? 'Target ≥ 90% tercapai' : 'Di bawah target 90%' }}
                        </p>
                        <p class="text-xs text-slate-500">
                            {{ $rupiah($feeInfo['collected'] ?? 0) }} dari {{ $rupiah($feeInfo['billed'] ?? 0) }}
                            {{ ($feeInfo['scope'] ?? '') === 'active_year' ? '(tahun ajaran '.($feeInfo['academic_year'] ?? 'aktif').')' : '(semua tagihan)' }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Komposisi Pengeluaran Tercatat</h3>
                @if(empty($metric->expense_distribution))
                    <p class="text-sm text-slate-500">Belum ada pengeluaran tercatat pada periode ini, jadi komposisinya belum bisa dihitung.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                        @foreach($metric->expense_distribution as $category => $percent)
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 space-y-2">
                                <span class="text-[10px] font-bold uppercase text-slate-500">{{ $labels[$category] ?? ucfirst(str_replace('_', ' ', $category)) }}</span>
                                <div class="text-2xl font-black text-slate-800 dark:text-white">{{ $percent }}%</div>
                                <div class="text-xs text-slate-500">{{ $rupiah($burn['totals'][$category] ?? 0) }}</div>
                                <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, (float) $percent) }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-slate-500">Belum ada metrik kesehatan keuangan.</p>
            </div>
        @endif
    </div>
</x-app-layout>
