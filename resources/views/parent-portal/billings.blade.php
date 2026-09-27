<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight tracking-tight">
                    Rincian Keuangan & <span class="bg-gradient-to-r from-primary-600 to-indigo-600 bg-clip-text text-transparent">Tagihan</span>
                </h2>
                <p class="text-sm font-semibold text-gray-500 mt-1">{{ school_name() }}</p>
            </div>
            
            <!-- Child Switcher Tab -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-1.5 rounded-2xl border border-gray-200/60 dark:border-gray-700/60 flex gap-2 shadow-sm">
                @foreach(auth()->user()->children as $child)
                    <a href="{{ route('parent-portal.billings', $child->id) }}" 
                       class="px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-300 flex items-center gap-2 {{ $student->id === $child->id ? 'bg-primary-600 text-white shadow-lg shadow-primary-900/10' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <span class="w-2.5 h-2.5 rounded-full {{ $student->id === $child->id ? 'bg-white' : 'bg-primary-500' }}"></span>
                        {{ $child->user->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <!-- Content Area -->
    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-xl flex items-center animate-fade-in">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-600 text-white p-4 rounded-2xl shadow-xl flex items-center animate-fade-in">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- 1. Student Summary Banner -->
            <div class="bg-gradient-to-r from-gray-900 via-indigo-950 to-slate-900 rounded-[2.5rem] p-8 shadow-2xl text-white relative overflow-hidden group">
                <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl flex items-center justify-center font-black text-2xl text-white">
                            {{ substr($student->user->name, 0, 1) }}
                        </div>
                        <div>
                            <span class="px-2.5 py-0.5 bg-white/10 border border-white/15 text-[10px] font-black uppercase tracking-wider rounded-full text-indigo-200">
                                {{ $student->classroom->name ?? 'Belum Ada Kelas' }}
                            </span>
                            <h3 class="text-2xl font-black mt-1.5 tracking-tight">{{ $student->user->name }}</h3>
                            <p class="text-xs text-gray-300 font-semibold mt-1">NIS: {{ $student->nis }} | NISN: {{ $student->nisn }}</p>
                        </div>
                    </div>

                    @php
                        $unpaidSum = $billings->where('status', '!=', 'paid')->sum('remaining_balance');
                        $paidSum = $billings->where('status', 'paid')->sum('amount') + $billings->where('status', 'partial')->sum(function($b) { return $b->amount - $b->remaining_balance; });
                    @endphp

                    <div class="flex flex-wrap gap-4 w-full md:w-auto">
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-4 min-w-[150px]">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Tunggakan</p>
                            <p class="text-xl font-black mt-1 {{ $unpaidSum > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                Rp {{ number_format($unpaidSum, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-4 min-w-[150px]">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Terbayar</p>
                            <p class="text-xl font-black mt-1 text-emerald-400">
                                Rp {{ number_format($paidSum, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Billings Ledger List -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-150/60 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h4 class="text-lg font-black text-gray-800 tracking-tight">Daftar Tagihan Siswa</h4>
                        <p class="text-xs text-gray-400 font-semibold mt-0.5">Daftar iuran sekolah rill beserta riwayat pembayarannya</p>
                    </div>
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-black uppercase tracking-wider">
                        {{ $billings->count() }} Total Tagihan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-8">Kategori / Nama Tagihan</th>
                                <th class="py-4 px-6 text-right">Jumlah Tagihan</th>
                                <th class="py-4 px-6 text-right">Sisa Tunggakan</th>
                                <th class="py-4 px-6 text-center">Jatuh Tempo</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-8 text-center">Aksi Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($billings as $billing)
                                <tr class="hover:bg-gray-50/50 transition-colors duration-250">
                                    <td class="py-5 px-8">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg {{ $billing->status === 'paid' ? 'bg-emerald-50 text-emerald-600' : ($billing->status === 'partial' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                                
                                            </div>
                                            <div>
                                                <h5 class="font-extrabold text-gray-800">{{ $billing->category->name }}</h5>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">ID: #{{ $billing->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-5 px-6 text-right font-bold text-gray-700">
                                        Rp {{ number_format($billing->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-5 px-6 text-right font-black {{ $billing->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}
                                    </td>
                                    <td class="py-5 px-6 text-center font-semibold text-gray-500">
                                        {{ \Carbon\Carbon::parse($billing->due_date)->isoFormat('D MMMM YYYY') }}
                                    </td>
                                    <td class="py-5 px-6 text-center">
                                        @if($billing->status === 'paid')
                                            <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-black uppercase tracking-wider border border-emerald-100">
                                                Lunas
                                            </span>
                                        @elseif($billing->status === 'partial')
                                            <span class="inline-flex px-3 py-1 bg-amber-50 text-amber-700 rounded-full text-xs font-black uppercase tracking-wider border border-amber-100">
                                                Dicicil
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-black uppercase tracking-wider border border-rose-100">
                                                Belum Bayar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-5 px-8 text-center">
                                        @if($billing->remaining_balance > 0)
                                            <a href="{{ route('parent-portal.billings.checkout', $billing->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-900/10 transition-all duration-300">
                                                Bayar Instan
                                            </a>
                                        @else
                                            <span class="text-xs font-black uppercase tracking-wider text-emerald-600 flex items-center justify-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Lunas
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400 italic font-bold">
                                         Tidak ada catatan tagihan keuangan untuk siswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Riwayat Transaksi / Payments Ledger -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-150/60 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100">
                    <h4 class="text-lg font-black text-gray-800 tracking-tight">Riwayat Pembayaran Berhasil</h4>
                    <p class="text-xs text-gray-400 font-semibold mt-0.5">Daftar transaksi pembayaran yang sukses divalidasi oleh sistem</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/75 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-8">No. Kuitansi</th>
                                <th class="py-4 px-6">Nama Tagihan</th>
                                <th class="py-4 px-6 text-center">Tanggal Pembayaran</th>
                                <th class="py-4 px-6 text-center">Metode</th>
                                <th class="py-4 px-6 text-right">Jumlah Dibayar</th>
                                <th class="py-4 px-8 text-center">Berkas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @php $hasPayments = false; @endphp
                            @foreach($billings as $billing)
                                @foreach($billing->payments as $payment)
                                    @php $hasPayments = true; @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors duration-250">
                                        <td class="py-4 px-8 font-extrabold text-indigo-600">
                                            {{ $payment->receipt_number }}
                                        </td>
                                        <td class="py-4 px-6 font-bold text-gray-800">
                                            {{ $billing->category->name }}
                                        </td>
                                        <td class="py-4 px-6 text-center text-gray-500 font-semibold">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->isoFormat('D MMMM YYYY HH:mm') }}
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="inline-flex px-2.5 py-0.5 bg-gray-100 border border-gray-200 text-gray-700 rounded-md text-[10px] font-black uppercase tracking-wider">
                                                {{ $payment->payment_method }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right font-black text-emerald-600">
                                            Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                                        </td>
                                        <td class="py-4 px-8 text-center">
                                            <a href="{{ route('finance.payments.receipt', $payment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-800 rounded-xl text-xs font-bold transition-colors duration-200">
                                                 Kuitansi
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if(!$hasPayments)
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-gray-400 italic font-bold">
                                        Belum ada riwayat transaksi pembayaran yang terekam.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
