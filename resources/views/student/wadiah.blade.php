<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest bg-primary-50 dark:bg-primary-950/30 px-3 py-1 rounded-full border border-primary-200/50 dark:border-primary-800/30">
                    Sistem Tabungan Wadiah
                </span>
                <h2 class="font-black text-3xl text-gray-900 dark:text-white leading-tight tracking-tight mt-2">
                    Dompet Digital <span class="bg-gradient-to-r from-primary-600 to-emerald-600 bg-clip-text text-transparent">Wadiah</span>
                </h2>
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 mt-1">Layanan transaksi cashless siswa SMA Islam Sultan Agung 1 Semarang</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider {{ $account->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30' : 'bg-rose-50 text-rose-700 border border-rose-200/80 dark:bg-rose-950/20 dark:text-rose-400 dark:border-rose-900/30' }}">
                    <span class="w-2 h-2 rounded-full {{ $account->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    {{ $account->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- Main Content Area -->
    <div class="py-8 bg-slate-50/50 dark:bg-slate-900/20 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="bg-emerald-600 text-white p-4 rounded-2xl shadow-xl flex items-center animate-fade-in">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Grid: Wallet Card (lg:col-span-7) -->
                <div class="lg:col-span-7 space-y-8">
                    <!-- Premium Physical Card Design -->
                    <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 rounded-[2.5rem] p-8 md:p-10 shadow-2xl text-white relative overflow-hidden group border border-white/10">
                        <div class="absolute -right-24 -bottom-24 w-96 h-96 bg-primary-600/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                        <div class="absolute -left-20 -top-20 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl"></div>
                        
                        <!-- Floating Glassmorphic blobs -->
                        <div class="absolute top-1/2 left-1/3 w-32 h-32 bg-primary-500/5 rounded-full blur-2xl animate-float"></div>
                        
                        <div class="relative z-10 flex flex-col justify-between h-full min-h-[300px]">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                                        Wadiah E-Wallet
                                    </span>
                                    <h3 class="text-xs font-semibold text-slate-400 mt-3 uppercase tracking-wider">Nomor Rekening</h3>
                                    <p class="text-xl font-mono font-bold tracking-widest text-slate-100 mt-1">
                                        {{ implode(' ', str_split($account->account_number, 4)) }}
                                    </p>
                                </div>
                                <div class="w-14 h-14 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center backdrop-blur-md shadow-inner">
                                    <svg class="w-8 h-8 text-primary-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Card Body: Balance -->
                            <div class="my-8">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Saldo Wadiah</span>
                                <h2 class="text-5xl md:text-6xl font-black tracking-tight text-white mt-1 flex items-baseline gap-1">
                                    <span class="text-2xl font-bold text-slate-400">Rp</span>
                                    <span>{{ number_format($account->balance, 0, ',', '.') }}</span>
                                </h2>
                            </div>
                            
                            <!-- Card Footer: Student Info and Spending Limits -->
                            <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs">
                                <div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Pemegang Kartu</span>
                                    <strong class="text-sm font-black text-white uppercase tracking-wide block mt-0.5">
                                        {{ $account->student->user->name }}
                                    </strong>
                                    <span class="text-[10px] text-slate-400">NIS: {{ $account->student->nis }}</span>
                                </div>
                                
                                <div class="bg-white/5 border border-white/10 p-3 rounded-2xl backdrop-blur-sm flex gap-4">
                                    <div>
                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Limit Jajan Harian</span>
                                        <strong class="text-white font-bold block mt-0.5">
                                            @if($account->daily_spending_limit > 0)
                                                Rp {{ number_format($account->daily_spending_limit, 0, ',', '.') }}
                                            @else
                                                <span class="text-emerald-400 font-black">UNLIMITED</span>
                                            @endif
                                        </strong>
                                    </div>
                                    @if($account->daily_spending_limit > 0)
                                        <div class="border-l border-white/10 pl-4">
                                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Sisa Limit Hari Ini</span>
                                            <strong class="text-emerald-400 font-bold block mt-0.5">
                                                Rp {{ number_format($account->remaining_daily_limit, 0, ',', '.') }}
                                            </strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Grid: Interactive QR Card (lg:col-span-5) -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-xl border border-slate-100 dark:border-gray-700/60 relative overflow-hidden h-full flex flex-col justify-between"
                         x-data="{
                             revealed: false,
                             nis: '{{ $account->student->nis }}',
                             timestamp: Math.floor(Date.now() / 1000),
                             qrUrl: '',
                             timer: null,
                             secondsLeft: 60,
                             generateCode() {
                                 this.timestamp = Math.floor(Date.now() / 1000);
                                 const code = `SISTA-${this.nis}-${this.timestamp}`;
                                 this.qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(code)}&color=0f172a&bgcolor=ffffff`;
                             },
                             startTimer() {
                                 this.secondsLeft = 60;
                                 if (this.timer) clearInterval(this.timer);
                                 this.timer = setInterval(() => {
                                     if (this.secondsLeft > 1) {
                                         this.secondsLeft--;
                                     } else {
                                         this.refreshQR();
                                     }
                                 }, 1000);
                             },
                             revealQR() {
                                 this.revealed = true;
                                 this.generateCode();
                                 this.startTimer();
                             },
                             refreshQR() {
                                 this.generateCode();
                                 this.startTimer();
                             }
                         }"
                         x-destroy="if (timer) clearInterval(timer)">
                        
                        <!-- Header -->
                        <div class="mb-6">
                            <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Metode Pembayaran</span>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">QR Code Pembayaran Cashless</h3>
                            <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">Tunjukkan kode QR ini ke kasir kantin untuk membayar secara cashless</p>
                        </div>
                        
                        <!-- QR Container -->
                        <div class="flex-1 flex flex-col items-center justify-center my-6">
                            <div class="relative w-64 h-64 bg-slate-50 dark:bg-gray-900/50 rounded-3xl p-4 border border-dashed border-slate-200 dark:border-gray-700/80 flex items-center justify-center overflow-hidden shadow-inner group">
                                
                                <!-- Decorative Corners -->
                                <div class="absolute top-2 left-2 w-6 h-6 border-t-4 border-l-4 border-primary-500 rounded-tl-lg"></div>
                                <div class="absolute top-2 right-2 w-6 h-6 border-t-4 border-r-4 border-primary-500 rounded-tr-lg"></div>
                                <div class="absolute bottom-2 left-2 w-6 h-6 border-b-4 border-l-4 border-primary-500 rounded-bl-lg"></div>
                                <div class="absolute bottom-2 right-2 w-6 h-6 border-b-4 border-r-4 border-primary-500 rounded-br-lg"></div>
                                
                                <!-- Blurred Overlay when hidden -->
                                <div class="absolute inset-0 bg-white/80 dark:bg-gray-800/85 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center transition-all duration-300"
                                     x-show="!revealed"
                                     x-transition:leave="opacity-0 scale-95">
                                    <div class="w-16 h-16 bg-primary-50 dark:bg-primary-950/40 rounded-full flex items-center justify-center text-primary-600 dark:text-primary-400 mb-4 shadow-sm">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-sm font-black text-gray-800 dark:text-slate-100 uppercase tracking-wide">QR Code Terkunci</h4>
                                    <p class="text-[11px] text-gray-500 mt-2 font-semibold">Ketuk tombol di bawah untuk menampilkan kode QR transaksi aman Anda</p>
                                    
                                    <button @click="revealQR()" class="mt-4 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all">
                                        Tampilkan QR Code
                                    </button>
                                </div>

                                <!-- QR Content -->
                                <div class="flex flex-col items-center justify-center" x-show="revealed" x-cloak>
                                    <img :src="qrUrl" class="w-48 h-48 rounded-xl object-contain bg-white p-1" alt="QR Code Transaksi">
                                    <p class="text-[10px] font-mono text-gray-400 dark:text-gray-500 mt-3" x-text="`SISTA-${nis}-${timestamp}`"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-4 border-t border-slate-100 dark:border-gray-700/60 pt-4 text-center" x-show="revealed" x-cloak>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Pembaruan Otomatis</span>
                                <span class="text-xs font-bold text-primary-600 dark:text-primary-400" x-text="`dalam ${secondsLeft} detik`"></span>
                            </div>
                            <button @click="refreshQR()" class="w-full py-3 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3-3m0 0l3 3m-3-3v12"></path>
                                </svg>
                                Perbarui QR Code
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Full Width Bottom Grid: Recent Transactions -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-slate-150/60 dark:border-gray-700/60">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Mutasi Rekening</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">Transaksi Terakhir</h3>
                    </div>
                    <span class="px-4 py-1.5 bg-slate-50 dark:bg-gray-900 text-gray-500 dark:text-slate-400 rounded-xl text-xs font-bold border border-slate-100 dark:border-gray-850">
                        {{ count($transactions) }} Transaksi Terbaru
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-gray-700/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <th class="pb-4 pl-4">Tanggal & Waktu</th>
                                <th class="pb-4">No. Referensi</th>
                                <th class="pb-4">Deskripsi</th>
                                <th class="pb-4">Tipe</th>
                                <th class="pb-4 pr-4 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/60 dark:divide-gray-700/50">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/30 transition-all">
                                    <!-- Date -->
                                    <td class="py-4 pl-4">
                                        <p class="text-sm font-black text-gray-800 dark:text-slate-100">
                                            {{ $transaction->created_at->translatedFormat('d M Y') }}
                                        </p>
                                        <p class="text-[11px] font-semibold text-gray-400 dark:text-slate-500">
                                            {{ $transaction->created_at->format('H:i') }}
                                        </p>
                                    </td>
                                    <!-- Reference -->
                                    <td class="py-4">
                                        <span class="text-xs font-mono font-bold text-gray-500 dark:text-slate-400 bg-slate-100 dark:bg-gray-900 px-2.5 py-1 rounded-lg">
                                            {{ $transaction->reference_number }}
                                        </span>
                                    </td>
                                    <!-- Description -->
                                    <td class="py-4 text-sm font-semibold text-gray-600 dark:text-slate-300">
                                        {{ $transaction->description }}
                                    </td>
                                    <!-- Type -->
                                    <td class="py-4">
                                        @if($transaction->type === 'deposit')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                SETORAN
                                            </span>
                                        @elseif($transaction->type === 'withdrawal')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                PENARIKAN
                                            </span>
                                        @elseif($transaction->type === 'purchase')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                PEMBELIAN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-50 text-slate-700 dark:bg-slate-900 dark:text-slate-400 border border-slate-200/50">
                                                {{ strtoupper($transaction->type) }}
                                            </span>
                                        @endif
                                    </td>
                                    <!-- Amount -->
                                    <td class="py-4 pr-4 text-right">
                                        <strong class="text-base font-black {{ $transaction->type === 'deposit' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            {{ $transaction->type === 'deposit' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400 dark:text-gray-500 italic font-bold">
                                        Belum ada riwayat transaksi pada rekening ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
