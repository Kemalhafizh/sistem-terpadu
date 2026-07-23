<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Statistik & Analisis Perpustakaan') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('library.circulation.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl font-bold text-xs hover:bg-gray-50 transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Kembali ke Sirkulasi
                </a>
                <a href="{{ route('library.overdue-report') }}" class="px-4 py-2 bg-red-50 text-red-600 border border-red-100 rounded-xl font-bold text-xs hover:bg-red-600 hover:text-white transition-all flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Buku Overdue
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Cards Ringkasan -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <!-- Buku Fisik -->
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Buku Fisik</span>
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_physical'] }}</span>
                    </div>
                </div>

                <!-- Buku Digital -->
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-4 bg-blue-50 rounded-2xl text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">E-Book Digital</span>
                        <span class="text-2xl font-black text-gray-900">{{ $stats['total_digital'] }}</span>
                    </div>
                </div>

                <!-- Peminjaman Aktif -->
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-4 bg-indigo-50 rounded-2xl text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Sedang Dipinjam</span>
                        <span class="text-2xl font-black text-gray-900">{{ $stats['active_loans'] }}</span>
                    </div>
                </div>

                <!-- Selesai Dikembalikan -->
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-4 bg-gray-50 rounded-2xl text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Selesai/Kembali</span>
                        <span class="text-2xl font-black text-gray-900">{{ $stats['returned_loans'] }}</span>
                    </div>
                </div>

                <!-- Terlambat (Overdue) -->
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="p-4 bg-rose-50 rounded-2xl text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest block">Terlambat (Denda)</span>
                        <span class="text-2xl font-black text-rose-600">{{ $stats['overdue_loans'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Charts & Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Trend Peminjaman (ApexCharts) -->
                <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm lg:col-span-2 space-y-4">
                    <h3 class="text-base font-black text-gray-800 uppercase tracking-wider">Tren Peminjaman Buku (6 Bulan)</h3>
                    <div id="trendChart"></div>
                </div>

                <!-- Distribusi Kategori (Donut) -->
                <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-4 flex flex-col justify-between">
                    <h3 class="text-base font-black text-gray-800 uppercase tracking-wider">Sebaran Kategori Koleksi</h3>
                    <div id="categoryChart"></div>
                </div>
            </div>

            <!-- Buku Terlaris -->
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm space-y-6">
                <h3 class="text-base font-black text-gray-800 uppercase tracking-wider">Top 5 Buku Paling Sering Dipinjam</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="pb-3 w-12 text-center">RANK</th>
                                <th class="pb-3 pl-4">JUDUL BUKU</th>
                                <th class="pb-3">KATEGORI</th>
                                <th class="pb-3">TIPE</th>
                                <th class="pb-3 text-center">TOTAL PEMINJAMAN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($stats['top_books'] as $index => $book)
                                <tr class="text-xs text-gray-700">
                                    <td class="py-4 text-center">
                                        <span class="w-6 h-6 rounded-lg font-black text-xs flex items-center justify-center mx-auto
                                            {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-100 text-slate-800' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td class="py-4 pl-4 font-black text-gray-900 uppercase">{{ $book->title }}</td>
                                    <td class="py-4 uppercase"><span class="px-2.5 py-1 bg-gray-50 text-gray-500 rounded-lg font-bold text-[9px] border border-gray-100">{{ $book->category->name }}</span></td>
                                    <td class="py-4 uppercase">
                                        @if($book->type == 'digital')
                                            <span class="text-emerald-600 font-bold">Digital</span>
                                        @else
                                            <span class="text-blue-600 font-bold">Fisik</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center font-black text-sm text-primary-600">{{ $book->loans_count }}x dipinjam</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada data transaksi peminjaman buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Line/Area Chart: Trend Peminjaman
            const trendOptions = {
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                series: [{
                    name: 'Jumlah Transaksi',
                    data: @json($stats['trend']['data'])
                }],
                xaxis: {
                    categories: @json($stats['trend']['labels']),
                    labels: { style: { colors: '#9ca3af', fontWeight: 'bold' } }
                },
                yaxis: {
                    labels: { style: { colors: '#9ca3af', fontWeight: 'bold' } }
                },
                colors: ['#10b981'], // Emerald 500
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f3f4f6' }
            };
            new ApexCharts(document.querySelector("#trendChart"), trendOptions).render();

            // 2. Donut Chart: Distribusi Kategori
            const categoryData = @json($stats['categories']);
            const catLabels = categoryData.map(c => c.name);
            const catSeries = categoryData.map(c => c.books_count);

            const catOptions = {
                chart: {
                    type: 'donut',
                    height: 300,
                    fontFamily: 'Inter, sans-serif'
                },
                series: catSeries,
                labels: catLabels,
                colors: ['#10b981', '#3b82f6', '#6366f1', '#a855f7', '#ec4899', '#f59e0b', '#374151'],
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Buku',
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { position: 'bottom', labels: { colors: '#4b5563', useSeriesColors: false } },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector("#categoryChart"), catOptions).render();
        });
    </script>
</x-app-layout>
