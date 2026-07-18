<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Merchant Hub</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight mt-1">
                    {{ $merchant->name }}
                </h2>
                <div class="flex items-center space-x-2 mt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">
                        Kasir: {{ auth()->user()->name }}
                    </span>
                </div>
            </div>
            
            <div class="flex space-x-2 p-1 bg-gray-100 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-800 shrink-0 self-start md:self-auto">
                <a href="{{ route('merchant.canteen.dashboard') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all bg-white dark:bg-gray-800 text-teal-600 dark:text-teal-400 shadow-sm">
                    Dashboard
                </a>
                <a href="{{ route('merchant.canteen.pos') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    POS Terminal
                </a>
                <a href="{{ route('merchant.canteen.settlement') }}" class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    Settlement
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Card 1: Today's Sales -->
            <div class="relative overflow-hidden bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80 transition-all duration-300 hover:shadow-md group">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-teal-500/5 dark:bg-teal-400/5 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Penjualan Hari Ini</span>
                        <h3 class="text-3xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-2">
                            Rp {{ number_format($todaySales, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs text-emerald-500 font-bold mt-1">Terpantau secara real-time</p>
                    </div>
                    <div class="p-4 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-2xl border border-teal-100 dark:border-teal-900/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Sales -->
            <div class="relative overflow-hidden bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80 transition-all duration-300 hover:shadow-md group">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-blue-500/5 dark:bg-blue-400/5 group-hover:scale-125 transition-transform duration-500"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Akumulasi Penjualan</span>
                        <h3 class="text-3xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-2">
                            Rp {{ number_format($totalSales, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs text-blue-500 font-bold mt-1">Total seluruh transaksi terproses</p>
                    </div>
                    <div class="p-4 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-2xl border border-blue-100 dark:border-blue-900/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80">
            <div class="mb-6">
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Grafik</span>
                <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-1">Tren Penjualan 7 Hari Terakhir</h3>
            </div>
            
            <div class="w-full min-h-[350px]" id="salesTrendChart"></div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
            <div class="p-8 border-b border-gray-50 dark:border-gray-700/50">
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Aktivitas</span>
                <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-1">Transaksi Terbaru</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-left text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700/50">
                            <th class="py-5 px-8">Siswa</th>
                            <th class="py-5 px-8">Produk Dipesan</th>
                            <th class="py-5 px-8 text-right">Total Transaksi</th>
                            <th class="py-5 px-8 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($recentTransactions as $tx)
                            <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="py-6 px-8">
                                    <p class="font-black text-gray-800 dark:text-gray-200">
                                        {{ $tx->wadiahTransaction->account->student->user->name ?? 'N/A' }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">
                                        NIS: {{ $tx->wadiahTransaction->account->student->nis ?? '-' }} • 
                                        Kelas: {{ $tx->wadiahTransaction->account->student->classroom->name ?? '-' }}
                                    </p>
                                </td>
                                <td class="py-6 px-8 max-w-xs">
                                    <div class="flex flex-wrap gap-1">
                                        @if(is_array($tx->items_ordered))
                                            @foreach($tx->items_ordered as $item)
                                                <span class="inline-block px-2.5 py-1 bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 rounded-lg text-[10px] font-semibold border border-gray-200/40 dark:border-gray-800/40">
                                                    {{ $item['name'] ?? '' }} (x{{ $item['qty'] ?? ($item['quantity'] ?? 1) }})
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-gray-500">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-right font-black text-teal-600 dark:text-teal-400 text-sm">
                                    Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-6 px-8 text-right text-xs text-gray-400 dark:text-gray-500 font-bold uppercase">
                                    {{ $tx->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 text-center text-gray-400 dark:text-gray-500 italic text-sm">
                                    Belum ada transaksi masuk hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ApexCharts Library -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chartData = @json($chartData);
            
            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    fontFamily: 'Inter, sans-serif'
                },
                series: [{
                    name: 'Penjualan',
                    data: chartData.sales
                }],
                xaxis: {
                    categories: chartData.labels,
                    labels: {
                        style: {
                            colors: '#9ca3af',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        },
                        style: {
                            colors: '#9ca3af',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                colors: ['#0d9488'], // Teal-600
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                grid: {
                    borderColor: '#e5e7eb',
                    strokeDashArray: 4,
                    padding: {
                        left: 20,
                        right: 20
                    },
                    xaxis: {
                        lines: {
                            show: false
                        }
                    },
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                        }
                    }
                }
            };
            
            // Adjust grid color based on dark mode status
            if (document.documentElement.classList.contains('dark')) {
                options.grid.borderColor = '#374151';
                options.tooltip.theme = 'dark';
            }

            const chart = new ApexCharts(document.querySelector("#salesTrendChart"), options);
            chart.render();

            // Listen to theme-changed events to update grid border colors dynamically
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: document.documentElement.classList.contains('dark') }));
            window.addEventListener('theme-changed', function(e) {
                const isDark = e.detail;
                chart.updateOptions({
                    grid: {
                        borderColor: isDark ? '#374151' : '#e5e7eb'
                    }
                });
            });
        });
    </script>
</x-app-layout>
