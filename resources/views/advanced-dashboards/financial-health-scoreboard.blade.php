<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Papan Skor Kesehatan Keuangan (Financial Health Scoreboard)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Transparansi tata kelola kas, rasio cadangan dana, dan kolektibilitas SPP YBWSA.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @if($metric)
            <!-- Financial Headline Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Cadangan Kas Sekolah</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        Rp {{ number_format($metric->cash_reserve, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-emerald-600 font-semibold">Kondisi Likuiditas Kuat</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Burn Rate Bulanan</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        Rp {{ number_format($metric->monthly_burn_rate, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-slate-500">Estimasi Pengeluaran Rutin</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Proyeksi Penerimaan</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white my-1">
                        Rp {{ number_format($metric->projected_revenue, 0, ',', '.') }}
                    </div>
                    <p class="text-xs text-cyan-600 font-semibold">Surplus Operasional Terkendali</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Tingkat Koleksi SPP</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">
                        {{ $metric->fee_collection_rate }}%
                    </div>
                    <p class="text-xs text-emerald-600 font-semibold">Target &gt; 90% Tercapai</p>
                </div>
            </div>

            <!-- Expense Breakdown & Cash Flow Allocation -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Alokasi Distribusi Anggaran Sekolah</h3>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 pt-2">
                    @foreach($metric->expense_distribution ?? [] as $category => $percent)
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 space-y-2">
                            <span class="text-[10px] font-bold uppercase text-slate-500">{{ ucfirst(str_replace('_', ' ', $category)) }}</span>
                            <div class="text-2xl font-black text-slate-800 dark:text-white">{{ $percent }}%</div>
                            <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $percent * 1.5 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-slate-500">Belum ada metrik kesehatan keuangan.</p>
            </div>
        @endif
    </div>
</x-app-layout>
