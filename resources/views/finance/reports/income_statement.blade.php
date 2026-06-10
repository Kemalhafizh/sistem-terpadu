<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                    📊 Laporan Keuangan Sekolah
                </h2>
                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">Sistem Akuntansi Buku Besar Terpadu</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all shadow-md shadow-gray-900/10 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-[calc(100vh-4rem)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
            
            <!-- Tab Navigasi Premium -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-2">
                <a href="{{ route('finance.reports.executive') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    📈 Ringkasan Eksekutif
                </a>
                <a href="{{ route('finance.reports.arrears') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    💸 Laporan Tunggakan
                </a>
                <a href="{{ route('finance.reports.projection') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    🔮 Proyeksi Pendapatan
                </a>
                <a href="{{ route('finance.reports.monthly') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    📅 Rekap Bulanan
                </a>
                <a href="{{ route('finance.reports.income') }}" class="px-4 py-2.5 rounded-xl text-xs font-black transition-all bg-emerald-50 text-emerald-700 shadow-sm">
                    📊 Laporan Laba Rugi
                </a>
                <a href="{{ route('finance.reports.balance') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    ⚖️ Neraca Keuangan
                </a>
                <a href="{{ route('finance.reports.ledger') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                    📖 Log Jurnal Umum
                </a>
            </div>

            <!-- Ringkasan Kinerja Laba Rugi -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Pendapatan Card -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-20 h-20 bg-emerald-50 rounded-full"></div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pendapatan (Revenues)</p>
                        <h3 class="text-3xl font-black text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                        <p class="text-[10px] text-gray-400 mt-2 font-semibold">Akumulasi seluruh pos pendapatan sekolah</p>
                    </div>
                </div>

                <!-- Beban Card -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-20 h-20 bg-red-50 rounded-full"></div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Beban (Expenses)</p>
                        <h3 class="text-3xl font-black text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                        <p class="text-[10px] text-gray-400 mt-2 font-semibold">Total biaya operasional & beban rilis</p>
                    </div>
                </div>

                <!-- Laba Bersih Card -->
                <div class="bg-gradient-to-br {{ $netIncome >= 0 ? 'from-emerald-600 to-teal-700' : 'from-red-600 to-rose-700' }} p-6 rounded-[2rem] shadow-xl text-white relative overflow-hidden">
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-lg"></div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-black text-emerald-100/80 uppercase tracking-widest mb-1">Laba Bersih (Net Income)</p>
                        <h3 class="text-3xl font-black">Rp {{ number_format($netIncome, 0, ',', '.') }}</h3>
                        <div class="mt-2 flex items-center text-[10px] font-bold">
                            <span class="px-2.5 py-0.5 bg-white/20 rounded-md">
                                {{ $netIncome >= 0 ? 'Surplus Operasional' : 'Defisit Operasional' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Laba Rugi -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                    <h4 class="text-lg font-black text-gray-800 flex items-center">
                        <span class="w-2 h-6 bg-emerald-500 rounded-full mr-3"></span> Rincian Pendapatan & Beban
                    </h4>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Periode Transaksi Berjalan</span>
                </div>
                
                <div class="p-8 space-y-8">
                    <!-- Bagian 1: Pendapatan -->
                    <div>
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">I. PENDAPATAN OPERASIONAL</h5>
                        <div class="divide-y divide-gray-50">
                            @forelse($revenueAccounts ?? [] as $account)
                                <div class="flex justify-between items-center py-4">
                                    <div>
                                        <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2.5 py-1 rounded-md mr-3">{{ $account->code }}</span>
                                        <span class="text-sm font-bold text-gray-700">{{ $account->name }}</span>
                                    </div>
                                    <span class="text-sm font-extrabold text-gray-800">Rp {{ number_format($account->total, 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-gray-400 italic">Tidak ada pos akun pendapatan yang terdaftar.</div>
                            @endforelse
                            <div class="flex justify-between items-center py-4 bg-emerald-50/30 px-4 rounded-xl mt-2 border-t border-emerald-100">
                                <span class="text-xs font-black text-emerald-800 uppercase tracking-wider">Subtotal Pendapatan</span>
                                <span class="text-sm font-black text-emerald-700">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 2: Beban -->
                    <div>
                        <h5 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-100 pb-2">II. BEBAN OPERASIONAL</h5>
                        <div class="divide-y divide-gray-50">
                            @forelse($expenseAccounts ?? [] as $account)
                                <div class="flex justify-between items-center py-4">
                                    <div>
                                        <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2.5 py-1 rounded-md mr-3">{{ $account->code }}</span>
                                        <span class="text-sm font-bold text-gray-700">{{ $account->name }}</span>
                                    </div>
                                    <span class="text-sm font-extrabold text-red-600">Rp {{ number_format($account->total, 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="py-6 text-center text-xs text-gray-400 italic">Tidak ada transaksi beban untuk periode ini.</div>
                            @endforelse
                            <div class="flex justify-between items-center py-4 bg-red-50/30 px-4 rounded-xl mt-2 border-t border-red-100">
                                <span class="text-xs font-black text-red-800 uppercase tracking-wider">Subtotal Beban</span>
                                <span class="text-sm font-black text-red-700">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian 3: Laba Bersih -->
                    <div class="pt-6 border-t-2 border-dashed border-gray-100 flex justify-between items-center bg-gray-900 text-white p-6 rounded-[2rem] mt-6 shadow-lg shadow-gray-900/10">
                        <div>
                            <h4 class="text-sm font-black uppercase tracking-widest">III. LABA BERSIH (NET INCOME)</h4>
                            <p class="text-[9px] text-gray-400 mt-1 uppercase font-bold tracking-wider">Hasil Bersih Setelah Dikurangi Beban Operasional</p>
                        </div>
                        <span class="text-2xl font-black {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            Rp {{ number_format($netIncome, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
