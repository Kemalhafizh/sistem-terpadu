<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m-10-6h3m12 0h3"></path>
                        </svg>
                    </span>
                    {{ __('Keuangan Kas OSIS') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • TRANSPARANSI & AKUNTABILITAS DATA KAS</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="$dispatch('open-modal', 'add-transaction-modal')" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Catat Kas Baru
                </button>
                
                <button type="button" @click="$dispatch('open-modal', 'export-pdf-modal')" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-750 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Cetak Laporan PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300 animate-fade-in">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- 3-Grid KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Stat 1: Total Pemasukan -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-emerald-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Total Pemasukan</p>
                        <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Seluruh penerimaan kas</p>
                    </div>
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3-3m0 0l3 3m-3-3v8m0-13a9 9 0 110 18 9 9 0 010-18z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 2: Total Pengeluaran -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-red-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Total Pengeluaran</p>
                        <h3 class="text-3xl font-extrabold text-red-600 dark:text-red-500">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Seluruh pengeluaran kegiatan</p>
                    </div>
                    <div class="w-14 h-14 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13l-3 3m0 0l-3-3m3 3V8m0-5a9 9 0 110 18 9 9 0 010-18z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 3: Saldo Aktif -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-teal-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Saldo Kas OSIS</p>
                        <h3 class="text-3xl font-extrabold text-teal-600 dark:text-teal-400">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Kas bersih saat ini</p>
                    </div>
                    <div class="w-14 h-14 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ApexCharts Visualizations -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Pie Chart 1: Income Categories -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <h4 class="text-base font-black text-gray-900 dark:text-white tracking-tight mb-6 flex items-center">
                        <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full mr-2.5"></span>
                        Alokasi Sumber Pemasukan
                    </h4>
                    <div id="osisIncomeChart" class="w-full h-72"></div>
                </div>

                <!-- Pie Chart 2: Expense Categories -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                    <h4 class="text-base font-black text-gray-900 dark:text-white tracking-tight mb-6 flex items-center">
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-2.5"></span>
                        Sebaran Alokasi Pengeluaran
                    </h4>
                    <div id="osisExpenseChart" class="w-full h-72"></div>
                </div>
            </div>

            <!-- Log Table -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-6">
                <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                        <span class="w-2 h-6 bg-emerald-600 rounded-full mr-3"></span>
                        Buku Besar Jurnal Kas OSIS
                    </h4>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Daftar riwayat transaksi kas masuk & keluar</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Jenis</th>
                                <th class="py-3 px-4">Deskripsi Transaksi</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Nominal</th>
                                <th class="py-3 px-4">Petugas</th>
                                <th class="py-3 px-4 text-center">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="py-4 px-4 text-gray-400 dark:text-gray-500">
                                        {{ $trx->transaction_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($trx->type === 'income')
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 rounded-lg text-[9px] font-black uppercase tracking-wider">Pemasukan</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30 rounded-lg text-[9px] font-black uppercase tracking-wider">Pengeluaran</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 font-extrabold text-gray-900 dark:text-white">
                                        {{ $trx->description }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 dark:text-gray-400">
                                        {{ $trx->category }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-black {{ $trx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $trx->type === 'income' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 dark:text-gray-400">
                                        {{ $trx->recorder->name }}
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        @if($trx->receipt_path)
                                            <a href="{{ asset('storage/' . $trx->receipt_path) }}" target="_blank" class="inline-flex items-center px-2 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-900/20 rounded-lg text-[10px] font-black uppercase tracking-wider transition-all">
                                                Berkas
                                            </a>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                        Belum ada data transaksi kas OSIS yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $transactions->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Catat Kas Baru -->
    <x-modal name="add-transaction-modal" focusable>
        <form method="POST" action="{{ route('osis.transactions.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Pencatatan Baru Kas OSIS</h3>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Lengkapi informasi pemasukan atau pengeluaran kas secara tepat</p>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="type" value="Jenis Transaksi" />
                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-xl shadow-sm font-semibold text-sm">
                        <option value="income">Kas Masuk (Pemasukan)</option>
                        <option value="expense">Kas Keluar (Pengeluaran)</option>
                    </select>
                </div>
                
                <div>
                    <x-input-label for="category" value="Kategori Kas" />
                    <select id="category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 dark:focus:border-emerald-600 focus:ring-emerald-500 dark:focus:ring-emerald-600 rounded-xl shadow-sm font-semibold text-sm">
                        <option value="Iuran">Iuran Bulanan Siswa</option>
                        <option value="Sponsor">Sponsorship / Sponsor</option>
                        <option value="Kegiatan">Pendanaan Kegiatan</option>
                        <option value="Perlengkapan">Perlengkapan / Aset</option>
                        <option value="Lainnya">Lainnya / Lain-lain</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="amount" value="Nominal Transaksi (Rupiah)" />
                    <x-text-input id="amount" name="amount" type="number" min="1" class="mt-1 block w-full" placeholder="100000" required />
                </div>
                
                <div>
                    <x-input-label for="transaction_date" value="Tanggal Transaksi" />
                    <x-text-input id="transaction_date" name="transaction_date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Deskripsi / Catatan Transaksi" />
                <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" placeholder="Pembelian Bola Basket Molten, dsb." required />
            </div>

            <div>
                <x-input-label for="receipt" value="Unggah Berkas Kuitansi / Bukti Transaksi" />
                <input id="receipt" name="receipt" type="file" accept="image/png, image/jpeg, image/jpg, application/pdf" class="mt-2 block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-emerald-50 dark:file:bg-emerald-950/30 file:text-emerald-700 dark:file:text-emerald-400 hover:file:bg-emerald-100 transition-all" />
                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-widest font-black">Jenis berkas: PNG, JPG, JPEG, PDF. Maksimal 10MB.</p>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-850 text-slate-700 dark:text-slate-350 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95 border border-emerald-500/20">
                    Simpan Transaksi Kas
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Cetak Laporan PDF -->
    <x-modal name="export-pdf-modal" focusable>
        <form method="GET" action="{{ route('osis.export') }}" class="p-6 space-y-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Cetak Laporan Keuangan Kas OSIS</h3>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Pilih filter rentang tanggal untuk mengunduh laporan PDF resmi</p>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="start_date" value="Tanggal Mulai" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" value="{{ now()->startOfMonth()->toDateString() }}" required />
                </div>
                
                <div>
                    <x-input-label for="end_date" value="Tanggal Selesai" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" value="{{ now()->toDateString() }}" required />
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-gray-850 text-slate-700 dark:text-slate-355 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                    Batal
                </button>
                <button type="submit" @click="$dispatch('close')" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/10 hover:shadow-emerald-600/20 active:scale-95 border border-emerald-500/20">
                    Ekspor Unduh PDF
                </button>
            </div>
        </form>
    </x-modal>

    <!-- ApexCharts Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Income Chart
            const incomeCategories = @json($categoriesIncome);
            const incomeLabels = Object.keys(incomeCategories);
            const incomeData = Object.values(incomeCategories).map(val => parseFloat(val));

            const incomeOptions = {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: incomeData.length > 0 ? incomeData : [100],
                labels: incomeLabels.length > 0 ? incomeLabels : ['Belum ada pemasukan'],
                colors: ['#10b981', '#34d399', '#6ee7b7', '#a7f3d0', '#ecfdf5'],
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Pemasukan',
                                    formatter: function (w) {
                                        return incomeData.length > 0 ? 'Rp ' + incomeData.reduce((a, b) => a + b, 0).toLocaleString('id-ID') : 'Rp 0';
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontWeight: 700,
                    fontSize: '11px',
                    markers: { radius: 12 }
                }
            };

            const incomeChart = new ApexCharts(document.querySelector("#osisIncomeChart"), incomeOptions);
            incomeChart.render();

            // Expense Chart
            const expenseCategories = @json($categoriesExpense);
            const expenseLabels = Object.keys(expenseCategories);
            const expenseData = Object.values(expenseCategories).map(val => parseFloat(val));

            const expenseOptions = {
                chart: {
                    type: 'donut',
                    height: 280,
                    fontFamily: 'Inter, sans-serif',
                    background: 'transparent'
                },
                theme: {
                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                },
                series: expenseData.length > 0 ? expenseData : [100],
                labels: expenseLabels.length > 0 ? expenseLabels : ['Belum ada pengeluaran'],
                colors: ['#ef4444', '#f87171', '#fca5a5', '#fecaca', '#fef2f2'],
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Pengeluaran',
                                    formatter: function (w) {
                                        return expenseData.length > 0 ? 'Rp ' + expenseData.reduce((a, b) => a + b, 0).toLocaleString('id-ID') : 'Rp 0';
                                    }
                                }
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontWeight: 700,
                    fontSize: '11px',
                    markers: { radius: 12 }
                }
            };

            const expenseChart = new ApexCharts(document.querySelector("#osisExpenseChart"), expenseOptions);
            expenseChart.render();
        });
    </script>
</x-app-layout>
