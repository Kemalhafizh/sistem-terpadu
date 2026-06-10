<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Rekap Keuangan Bulanan') }}
            </h2>
            <div class="flex items-center space-x-3">
                <form action="{{ route('finance.reports.monthly') }}" method="GET" class="flex items-center space-x-2">
                    <select name="month" class="rounded-xl border-gray-100 text-xs font-bold focus:border-primary-500 focus:ring-primary-500">
                        @foreach($monthsList ?? [] as $mIdx => $mName)
                            <option value="{{ $mIdx }}" {{ $month == $mIdx ? 'selected' : '' }}>
                                {{ $mName }}
                            </option>
                        @endforeach
                    </select>
                    <select name="year" class="rounded-xl border-gray-100 text-xs font-bold focus:border-primary-500 focus:ring-primary-500">
                        @foreach($yearsList ?? [] as $yVal)
                            <option value="{{ $yVal }}" {{ $year == $yVal ? 'selected' : '' }}>
                                {{ $yVal }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all">Filter</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Back to Executive Report Link -->
            <div class="flex justify-between items-center">
                <a href="{{ route('finance.reports.executive') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Dashboard Eksekutif
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Monthly Revenue -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Pendapatan Bulan Ini</p>
                    <h3 class="text-2xl font-black text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Akumulasi kredit akun pendapatan (4xxx)</p>
                </div>
                <!-- Monthly Expense -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Beban/Pengeluaran Bulan Ini</p>
                    <h3 class="text-2xl font-black text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Akumulasi debit akun beban (5xxx)</p>
                </div>
                <!-- Monthly Net Profit/Loss -->
                <div class="{{ $netIncome >= 0 ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }} p-6 rounded-[2rem] shadow-xl">
                    <p class="text-[10px] font-black {{ $netIncome >= 0 ? 'text-emerald-100' : 'text-rose-100' }} uppercase tracking-widest mb-1">Selisih Bersih (Laba/Rugi)</p>
                    <h3 class="text-2xl font-black">Rp {{ number_format($netIncome, 0, ',', '.') }}</h3>
                    <p class="text-[10px] {{ $netIncome >= 0 ? 'text-emerald-150' : 'text-rose-150' }} mt-2 font-bold italic">Hasil selisih bersih operasional</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Revenue Accounts Breakdown -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6">
                    <h4 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center border-b border-gray-50 pb-4">
                        <span class="w-2 h-5 bg-emerald-500 rounded-full mr-2.5"></span> Rincian Pendapatan (Inflow)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="pb-3 pl-2">Kode Akun</th>
                                    <th class="pb-3">Nama Rekening Pendapatan</th>
                                    <th class="pb-3 text-right pr-2">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($revenueAccounts as $revAcc)
                                    <tr class="text-xs text-gray-700 hover:bg-gray-50/50 transition-all">
                                        <td class="py-4 pl-2 font-bold text-gray-400">{{ $revAcc->code }}</td>
                                        <td class="py-4 font-black text-gray-900 uppercase">{{ $revAcc->name }}</td>
                                        <td class="py-4 text-right pr-2 font-black text-emerald-600">Rp {{ number_format($revAcc->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-400 italic">Tidak ada postingan transaksi pendapatan bulan ini.</td>
                                    </tr>
                                @endforelse
                                @if($revenueAccounts->count() > 0)
                                    <tr class="border-t-2 border-gray-100 font-black text-xs">
                                        <td colspan="2" class="py-4 pl-2 text-gray-800">TOTAL PENDAPATAN</td>
                                        <td class="py-4 text-right pr-2 text-emerald-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expense Accounts Breakdown -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-6">
                    <h4 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center border-b border-gray-50 pb-4">
                        <span class="w-2 h-5 bg-red-500 rounded-full mr-2.5"></span> Rincian Beban & Pengeluaran (Outflow)
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                    <th class="pb-3 pl-2">Kode Akun</th>
                                    <th class="pb-3">Nama Rekening Beban</th>
                                    <th class="pb-3 text-right pr-2">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($expenseAccounts as $expAcc)
                                    <tr class="text-xs text-gray-700 hover:bg-gray-50/50 transition-all">
                                        <td class="py-4 pl-2 font-bold text-gray-400">{{ $expAcc->code }}</td>
                                        <td class="py-4 font-black text-gray-900 uppercase">{{ $expAcc->name }}</td>
                                        <td class="py-4 text-right pr-2 font-black text-red-600">Rp {{ number_format($expAcc->total, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-gray-400 italic">Tidak ada postingan transaksi pengeluaran/beban bulan ini.</td>
                                    </tr>
                                @endforelse
                                @if($expenseAccounts->count() > 0)
                                    <tr class="border-t-2 border-gray-100 font-black text-xs">
                                        <td colspan="2" class="py-4 pl-2 text-gray-800">TOTAL BEBAN</td>
                                        <td class="py-4 text-right pr-2 text-red-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
