<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                     Neraca Keuangan (Balance Sheet)
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
                     Ringkasan Eksekutif
                </a>
                <a href="{{ route('finance.reports.arrears') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Tunggakan
                </a>
                <a href="{{ route('finance.reports.projection') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Proyeksi Pendapatan
                </a>
                <a href="{{ route('finance.reports.monthly') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Rekap Bulanan
                </a>
                <a href="{{ route('finance.reports.income') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Laba Rugi
                </a>
                <a href="{{ route('finance.reports.balance') }}" class="px-4 py-2.5 rounded-xl text-xs font-black transition-all bg-emerald-50 text-emerald-700 shadow-sm">
                     Neraca Keuangan
                </a>
                <a href="{{ route('finance.reports.ledger') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Log Jurnal Umum
                </a>
            </div>

            <!-- Balanced Indicator Card -->
            @php
                $difference = abs($totalAssets - $totalLiabilitiesAndEquity);
                $isBalanced = $difference < 0.01;
            @endphp
            <div class="bg-gradient-to-r {{ $isBalanced ? 'from-emerald-500 to-teal-600' : 'from-amber-500 to-rose-600' }} rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-white/20 rounded-2xl">
                            @if($isBalanced)
                                <svg class="w-6 h-6 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-black text-lg tracking-tight">
                                {{ $isBalanced ? 'Neraca Keuangan Seimbang (Balanced Ledger)' : 'Buku Besar Membutuhkan Penyesuaian' }}
                            </h4>
                            <p class="text-emerald-50/90 font-medium text-xs mt-0.5">
                                {{ $isBalanced 
                                    ? 'Selamat! Persamaan Akuntansi (Aset = Kewajiban + Ekuitas) terpenuhi 100% secara akurat.' 
                                    : 'Terdapat selisih nominal pembukuan yang harus ditinjau ulang oleh staf keuangan.' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black text-emerald-100 uppercase tracking-wider block">Selisih Persamaan</span>
                        <span class="text-xl font-mono font-black">Rp {{ number_format($difference, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Rincian Neraca Sisi Kiri dan Sisi Kanan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- SISI KIRI: ASET -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/40">
                            <h4 class="text-md font-black text-gray-800 flex items-center">
                                <span class="w-2.5 h-5 bg-emerald-500 rounded-full mr-2.5"></span> AKTIVA (ASSETS)
                            </h4>
                            <span class="text-[9px] font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-wider">Debit Balance</span>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <div>
                                <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pb-1 border-b border-gray-50">Aset Lancar</h5>
                                <div class="divide-y divide-gray-50">
                                    @forelse($assetAccounts ?? [] as $account)
                                        <div class="flex justify-between items-center py-4">
                                            <div>
                                                <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded mr-2.5">{{ $account->code }}</span>
                                                <span class="text-xs font-bold text-gray-700">{{ $account->name }}</span>
                                            </div>
                                            <span class="text-xs font-extrabold text-gray-800">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <div class="py-4 text-center text-xs text-gray-400 italic">Tidak ada akun aset terdaftar.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sisi Aset -->
                    <div class="p-8 bg-gray-50/80 border-t border-gray-100 flex justify-between items-center rounded-b-[2.5rem]">
                        <span class="text-xs font-black text-gray-800 uppercase tracking-wider">TOTAL AKTIVA (ASET)</span>
                        <span class="text-lg font-black text-emerald-600">Rp {{ number_format($totalAssets, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- SISI KANAN: KEWAJIBAN & EKUITAS -->
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/40">
                            <h4 class="text-md font-black text-gray-800 flex items-center">
                                <span class="w-2.5 h-5 bg-indigo-500 rounded-full mr-2.5"></span> PASIVA (LIABILITIES & EQUITY)
                            </h4>
                            <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full uppercase tracking-wider">Credit Balance</span>
                        </div>
                        
                        <div class="p-8 space-y-6">
                            <!-- Kewajiban -->
                            <div>
                                <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pb-1 border-b border-gray-50">Kewajiban / Liabilitas</h5>
                                <div class="divide-y divide-gray-50">
                                    @forelse($liabilityAccounts ?? [] as $account)
                                        <div class="flex justify-between items-center py-4">
                                            <div>
                                                <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded mr-2.5">{{ $account->code }}</span>
                                                <span class="text-xs font-bold text-gray-700">{{ $account->name }}</span>
                                            </div>
                                            <span class="text-xs font-extrabold text-gray-800">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <div class="py-4 text-center text-[10px] text-gray-400 italic">Tidak ada pos kewajiban berjalan.</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Ekuitas -->
                            <div>
                                <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 pb-1 border-b border-gray-50">Ekuitas & Modal</h5>
                                <div class="divide-y divide-gray-50">
                                    @forelse($equityAccounts ?? [] as $account)
                                        <div class="flex justify-between items-center py-4">
                                            <div>
                                                <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded mr-2.5">{{ $account->code }}</span>
                                                <span class="text-xs font-bold text-gray-700">{{ $account->name }}</span>
                                            </div>
                                            <span class="text-xs font-extrabold text-gray-800">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <!-- No custom equity entries, typically 0 modal -->
                                    @endforelse
                                    
                                    <!-- Laba Berjalan (Net Income dari Laba Rugi) -->
                                    <div class="flex justify-between items-center py-4">
                                        <div>
                                            <span class="text-xs font-mono bg-gray-100 text-gray-500 px-2 py-0.5 rounded mr-2.5">RE-CUR</span>
                                            <span class="text-xs font-bold text-gray-700">Laba Bersih Tahun Berjalan</span>
                                        </div>
                                        <span class="text-xs font-extrabold text-emerald-600">Rp {{ number_format($currentNetIncome, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sisi Pasiva -->
                    <div class="p-8 bg-gray-50/80 border-t border-gray-100 flex justify-between items-center rounded-b-[2.5rem]">
                        <span class="text-xs font-black text-gray-800 uppercase tracking-wider">TOTAL PASIVA (KEWAJIBAN & EKUITAS)</span>
                        <span class="text-lg font-black text-indigo-600">Rp {{ number_format($totalLiabilitiesAndEquity, 0, ',', '.') }}</span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
