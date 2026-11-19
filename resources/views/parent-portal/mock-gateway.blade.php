<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-900 leading-tight">
             Gerbang Simulasi <span class="bg-gradient-to-r from-indigo-600 to-primary-600 bg-clip-text text-transparent">Pembayaran (Mock Gateway)</span>
        </h2>
    </x-slot>

    <div class="py-12 bg-slate-950 text-white min-h-[calc(100vh-4rem)] flex items-center">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 w-full space-y-8 animate-fade-in relative z-10">
            <div class="bg-slate-900 border border-slate-800/80 rounded-3xl p-8 shadow-xl relative overflow-hidden">
                <!-- Status Badge -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full"></span>
                        <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest bg-emerald-950/40 border border-emerald-900/40 px-3 py-1 rounded-full">SANDBOX ACTIVE</span>
                    </div>
                    <span class="text-[10px] font-black text-slate-500 tracking-wider">SECURE ID: #{{ strtoupper(Str::random(6)) }}</span>
                </div>

                <!-- Credit Card Visual Layout -->
                <div class="bg-gradient-to-br from-indigo-700 via-indigo-900 to-slate-900 rounded-3xl p-6 shadow-xl relative overflow-hidden mb-8 border border-indigo-500/20 group">
                    <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/5 rounded-full blur-xl group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-bold text-indigo-200 uppercase tracking-widest">Sistem Terpadu Bank</p>
                            <h4 class="text-xl font-black mt-2 tracking-wide">MOCK TRANSACTION</h4>
                        </div>
                        <span class="text-2xl"></span>
                    </div>
                    
                    <div class="mt-8">
                        <p class="text-[9px] text-indigo-300 font-bold uppercase tracking-wider">Total Pembayaran</p>
                        <h3 class="text-3xl font-black mt-1 tracking-tight">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</h3>
                    </div>

                    <div class="mt-6 flex justify-between items-center text-[10px] font-mono text-indigo-200">
                        <span>ORDER: BILL-{{ $billing->id }}</span>
                        <span>EXPIRES: 15 MIN</span>
                    </div>
                </div>

                <!-- Transaction Details -->
                <div class="space-y-4 border-t border-slate-800/80 pt-6 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Nama Siswa</span>
                        <span class="font-extrabold text-slate-200">{{ $student->user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Kategori Iuran</span>
                        <span class="font-extrabold text-slate-200">{{ $billing->category->name }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-6 border-b border-slate-800/80">
                        <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider">Akun Orang Tua</span>
                        <span class="font-extrabold text-slate-200">{{ auth()->user()->name }}</span>
                    </div>
                </div>

                <!-- Interaction Actions -->
                <div class="mt-8 space-y-4">
                    <form action="{{ route('parent-portal.billings.callback', $billing->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-center py-4 rounded-xl font-black text-xs tracking-wider shadow-lg shadow-emerald-500/10 hover:from-emerald-600 hover:to-teal-600 active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>SIMULASIKAN BAYAR LUNAS (SUKSES)</span>
                        </button>
                    </form>

                    <a href="{{ route('parent-portal.billings', $student) }}" class="block w-full bg-slate-800 hover:bg-slate-700 border border-slate-700/50 text-slate-300 text-center py-4 rounded-xl font-black text-xs tracking-wider active:scale-[0.98] transition-all flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>BATALKAN TRANSAKSI / KEMBALI</span>
                    </a>
                </div>
            </div>

            <p class="text-[9px] text-center text-slate-500 uppercase tracking-widest">
                {{ mb_strtoupper(school_name()) }} • PORTAL PENGEMBANGAN
            </p>
        </div>
    </div>
</x-app-layout>
