<x-app-layout>
    <x-slot name="title">Penarikan Tunai Wadiah</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] uppercase tracking-widest text-rose-600 dark:text-rose-455 font-black">TRANSAKSI TABUNGAN</span>
                <h2 class="font-black text-3xl text-gray-805 dark:text-white leading-tight mt-1">
                    📤 Penarikan Tunai Wadiah
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Proses penarikan saldo tabungan wadiah siswa secara manual.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.finance.wadiah.show', $account->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-2xl py-2.5 px-5 text-xs font-bold transition-all flex items-center gap-2">
                    ← Kembali ke Detail Rekening
                </a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{ amount: '', balance: {{ (float) $account->balance }} }">
        <!-- Student Details Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-36 h-36 bg-rose-500/10 dark:bg-rose-500/5 rounded-full blur-2xl"></div>
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-rose-50 dark:bg-rose-900/20 text-rose-605 dark:text-rose-450 rounded-[2rem] flex items-center justify-center text-4xl shadow-inner mb-4">
                        👤
                    </div>
                    <span class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest mb-1">DATA PEMILIK REKENING</span>
                    <h3 class="font-black text-xl text-gray-800 dark:text-white leading-snug">{{ $account->student->user->name }}</h3>
                    <p class="text-xs text-gray-400 font-semibold mt-1">NIS: {{ $account->student->nis }}</p>
                    <span class="mt-3 px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-extrabold text-[10px] rounded-lg">
                        Kelas: {{ $account->student->classroom->name ?? '-' }}
                    </span>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-750">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs text-gray-400 font-bold">Nomor Rekening:</span>
                        <span class="font-mono font-bold text-sm text-teal-600 dark:text-teal-400">{{ $account->account_number }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-400 font-bold">Saldo Saat Ini:</span>
                        <span class="font-black text-lg text-rose-600 dark:text-rose-400">Rp {{ number_format($account->balance, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50/80 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-900/30 rounded-[2rem] p-6 text-blue-805 dark:text-blue-300 flex items-start gap-4">
                <span class="text-2xl mt-0.5">ℹ️</span>
                <div class="text-xs space-y-1">
                    <h4 class="font-black text-blue-900 dark:text-blue-250">Batas Penarikan Harian</h4>
                    <p class="leading-relaxed text-blue-700 dark:text-blue-300">Harap pastikan jumlah penarikan tidak mengganggu limit harian jajan anak jika rekening digunakan untuk kartu kantin cashless sekolah.</p>
                </div>
            </div>
        </div>

        <!-- Withdraw Form Card -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-150/60 dark:border-gray-700/50 shadow-xl p-8">
                <span class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">INPUT TRANSAKSI</span>
                <h3 class="font-black text-xl text-gray-800 dark:text-white mb-6">Formulir Penarikan Tunai</h3>

                <form action="{{ route('admin.finance.wadiah.withdraw.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="wadiah_account_id" value="{{ $account->id }}">

                    <div class="space-y-2">
                        <label for="amount" class="block text-xs font-black text-gray-450 dark:text-gray-450 uppercase tracking-wider">Jumlah Penarikan (Rupiah) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-lg font-black text-gray-400">Rp</span>
                            <input type="number" id="amount" name="amount" min="1000" step="1000" x-model.number="amount" placeholder="Minimal Rp 1.000" class="w-full pl-14 pr-5 py-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[1.5rem] text-lg font-black text-gray-800 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none placeholder-gray-400" required>
                        </div>
                        
                        <!-- Alpine.js real-time validation warning -->
                        <div x-show="amount > balance" x-transition.opacity class="mt-2 p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/35 rounded-2xl text-rose-750 dark:text-rose-400 text-xs font-bold flex items-center gap-2" style="display: none;">
                            <span>⚠️</span>
                            <span>Peringatan: Jumlah penarikan melebihi saldo saat ini (Sisa Saldo: Rp {{ number_format($account->balance, 0, ',', '.') }}). Transaksi mungkin ditolak.</span>
                        </div>
                        
                        @error('amount')
                            <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="description" class="block text-xs font-black text-gray-450 dark:text-gray-450 uppercase tracking-wider">Keterangan / Catatan</label>
                        <textarea id="description" name="description" rows="3" placeholder="Contoh: Penarikan tunai oleh siswa / wali murid..." class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-[1.5rem] text-sm text-gray-855 dark:text-white focus:ring-2 focus:ring-rose-500 focus:outline-none placeholder-gray-400"></textarea>
                        @error('description')
                            <p class="text-xs text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row gap-4">
                        <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white rounded-[1.5rem] py-4 px-6 text-sm font-black uppercase tracking-wider transition-all shadow-lg shadow-rose-500/20">
                            Proses Penarikan
                        </button>
                        <a href="{{ route('admin.finance.wadiah.show', $account->id) }}" class="text-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-205 dark:hover:bg-gray-600 text-gray-700 dark:text-white rounded-[1.5rem] py-4 px-6 text-sm font-black uppercase tracking-wider transition-all">
                            Batalkan
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
