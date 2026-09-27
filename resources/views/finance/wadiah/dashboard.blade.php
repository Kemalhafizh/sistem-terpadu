<x-app-layout>
    <x-slot name="title">Dashboard Tabungan Wadiah</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-teal-600 dark:text-teal-400 font-black">SISTEM KEUANGAN SYARIAH</span>
                <h2 class="font-black text-3xl text-gray-805 dark:text-white leading-tight mt-1">
                     Dashboard Tabungan Wadiah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Kelola simpanan wadiah dan transaksi cashless siswa {{ school_name() }}.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.finance.wadiah.index') }}" class="bg-teal-600 hover:bg-teal-700 text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all shadow-lg shadow-teal-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Daftar Rekening
                </a>
                <a href="{{ route('admin.finance.wadiah.merchants') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Daftar Merchant/Kantin
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8">
        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Total Wadiah Savings -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl shadow-slate-100/40 dark:shadow-none p-8 flex items-center gap-6 relative overflow-hidden transition-all hover:scale-[1.02]">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-teal-500/10 dark:bg-teal-500/5 rounded-full blur-2xl"></div>
                <div class="w-16 h-16 bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-3xl flex items-center justify-center text-3xl shadow-inner shrink-0">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Saldo Wadiah</span>
                    <h3 class="font-black text-2xl lg:text-3xl text-gray-850 dark:text-white mt-1">
                        Rp {{ number_format($totalSavings, 0, ',', '.') }}
                    </h3>
                    <p class="text-[10px] font-bold text-teal-655 text-teal-600 dark:text-teal-400 mt-1">Simpanan Amanah Siswa</p>
                </div>
            </div>

            <!-- Card 2: Active Accounts -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl shadow-slate-100/40 dark:shadow-none p-8 flex items-center gap-6 relative overflow-hidden transition-all hover:scale-[1.02]">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-blue-500/10 dark:bg-blue-500/5 rounded-full blur-2xl"></div>
                <div class="w-16 h-16 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-3xl flex items-center justify-center text-3xl shadow-inner shrink-0">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Rekening Aktif</span>
                    <h3 class="font-black text-2xl lg:text-3xl text-gray-850 dark:text-white mt-1">
                        {{ number_format($activeAccountsCount, 0, ',', '.') }}
                    </h3>
                    <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 mt-1">Siswa Terdaftar</p>
                </div>
            </div>

            <!-- Card 3: Total Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl shadow-slate-100/40 dark:shadow-none p-8 flex items-center gap-6 relative overflow-hidden transition-all hover:scale-[1.02]">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full blur-2xl"></div>
                <div class="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-3xl flex items-center justify-center text-3xl shadow-inner shrink-0">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Transaksi</span>
                    <h3 class="font-black text-2xl lg:text-3xl text-gray-850 dark:text-white mt-1">
                        {{ number_format($totalTransactionsCount, 0, ',', '.') }}
                    </h3>
                    <p class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 mt-1">Transaksi Diproses</p>
                </div>
            </div>
        </div>

        <!-- Chart and Action Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-6 md:p-8 lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest block">ANALISIS AKTIVITAS 7 HARI TERAKHIR</span>
                        <h3 class="font-black text-xl text-gray-800 dark:text-white mt-1"> Perbandingan Setoran vs Penarikan</h3>
                    </div>
                </div>
                <div class="w-full">
                    <div id="wadiahChart" class="min-h-[350px]"></div>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-6 md:p-8 lg:col-span-1 flex flex-col justify-between">
                <div>
                    <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">AKSI CEPAT</span>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white mb-6"> Operasi Keuangan</h3>
                    
                    <div class="space-y-4">
                        <a href="{{ route('admin.finance.wadiah.index') }}" class="group flex items-center gap-4 p-4 rounded-3xl bg-teal-50/50 dark:bg-teal-900/10 border border-teal-100/50 dark:border-teal-900/20 hover:bg-teal-50 dark:hover:bg-teal-900/25 transition-all">
                            <div class="w-10 h-10 bg-teal-600 text-white rounded-2xl flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform">
                                
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-gray-800 dark:text-white">Setor Tunai</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Lakukan penyetoran saldo ke rekening siswa</span>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.finance.wadiah.index') }}" class="group flex items-center gap-4 p-4 rounded-3xl bg-rose-50/50 dark:bg-rose-900/10 border border-rose-100/50 dark:border-rose-900/20 hover:bg-rose-50 dark:hover:bg-rose-900/25 transition-all">
                            <div class="w-10 h-10 bg-rose-605 bg-rose-600 text-white rounded-2xl flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform">
                                
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-gray-800 dark:text-white">Tarik Tunai</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Proses penarikan saldo wadiah siswa</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.finance.wadiah.merchants') }}" class="group flex items-center gap-4 p-4 rounded-3xl bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100/50 dark:border-indigo-900/20 hover:bg-indigo-50 dark:hover:bg-indigo-900/25 transition-all">
                            <div class="w-10 h-10 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-lg shadow-md group-hover:scale-110 transition-transform">
                                
                            </div>
                            <div>
                                <span class="block font-bold text-sm text-gray-800 dark:text-white">Register Merchant</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">Daftarkan tenant kantin baru</span>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700/55 text-center">
                    <span class="text-[10px] text-gray-400 font-semibold leading-relaxed block">
                        Sistem Keuangan Wadiah terintegrasi dengan Kartu Siswa Cashless untuk jajan di Kantin Sekolah.
                    </span>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-6 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest block">RIWAYAT AKTIVITAS TERBARU</span>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white mt-1"> 10 Transaksi Terakhir</h3>
                </div>
                <a href="{{ route('admin.finance.wadiah.index') }}" class="text-xs font-black text-teal-600 dark:text-teal-400 hover:underline">
                    LIHAT SEMUA TRANSAKSI →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/55">
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Siswa</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">NIS</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Tipe</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500 text-right">Jumlah</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500">Diproses Oleh</th>
                            <th class="py-4 text-[10px] font-black uppercase tracking-widest text-gray-450 dark:text-gray-500 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-750">
                        @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-4 font-bold text-sm text-gray-800 dark:text-white">
                                    {{ $tx->account->student->user->name ?? 'Tidak Diketahui' }}
                                </td>
                                <td class="py-4 text-xs font-semibold text-gray-400">
                                    {{ $tx->account->student->nis ?? '-' }}
                                </td>
                                <td class="py-4">
                                    @if($tx->type === 'deposit')
                                        <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                            Setoran
                                        </span>
                                    @elseif($tx->type === 'withdrawal')
                                        <span class="px-2.5 py-1 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-450 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                            Penarikan
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 font-extrabold text-[10px] rounded-lg uppercase tracking-wider">
                                            {{ $tx->type }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 font-black text-sm text-right @if($tx->type === 'deposit') text-emerald-600 dark:text-emerald-400 @else text-gray-800 dark:text-white @endif">
                                    @if($tx->type === 'deposit') + @else - @endif Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 text-xs text-gray-500 dark:text-gray-400 font-semibold">
                                    {{ $tx->processedBy->name ?? 'Merchant / Sistem' }}
                                </td>
                                <td class="py-4 text-xs text-gray-450 dark:text-gray-500 text-right font-medium">
                                    {{ $tx->created_at->translatedFormat('d M Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-gray-400 italic">
                                    Belum ada riwayat transaksi wadiah terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartData = @json($chartData);
            
            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                colors: ['#0d9488', '#f43f5e'],
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                series: [
                    {
                        name: 'Setoran (Deposit)',
                        data: chartData.deposits
                    },
                    {
                        name: 'Penarikan (Withdrawal)',
                        data: chartData.withdrawals
                    }
                ],
                xaxis: {
                    categories: chartData.labels,
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        },
                        style: {
                            colors: '#9ca3af',
                            fontSize: '11px',
                            fontWeight: 650
                        }
                    }
                },
                grid: {
                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#f1f5f9',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                    y: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: {
                        colors: document.documentElement.classList.contains('dark') ? '#ffffff' : '#374151',
                        useSeriesColors: false
                    },
                    markers: {
                        radius: 12
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#wadiahChart"), options);
            chart.render();

            // Listen for theme changes to dynamically switch chart background
            window.addEventListener('theme-changed', function(e) {
                const isDark = e.detail;
                chart.updateOptions({
                    theme: {
                        mode: isDark ? 'dark' : 'light'
                    },
                    grid: {
                        borderColor: isDark ? '#374151' : '#f1f5f9'
                    },
                    legend: {
                        labels: {
                            colors: isDark ? '#ffffff' : '#374151'
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
