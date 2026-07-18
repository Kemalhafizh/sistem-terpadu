<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('portal.charity.portal') }}" 
               class="p-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 rounded-xl transition-all"
               title="Kembali ke Portal">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Pembayaran Donasi</span>
                <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    Konfirmasi Berdonasi
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-4" 
         x-data="{ 
             paymentMethod: 'wadiah_wallet', 
             isAnonymous: false, 
             amount: '', 
             walletBalance: {{ $walletBalance }},
             isStudent: {{ auth()->user()->role === 'student' ? 'true' : 'false' }}
         }">

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 p-4 rounded-2xl shadow-sm text-red-800 dark:text-red-300 font-bold text-sm mb-6 flex items-center space-x-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Campaign Summary Details (Left/Top) -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                    <span class="inline-block px-3 py-1 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                        {{ $campaign->type }}
                    </span>
                    <h3 class="font-black text-lg text-gray-800 dark:text-white leading-tight">
                        {{ $campaign->title }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-4">
                        {{ $campaign->description }}
                    </p>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3">
                        <div class="flex justify-between items-center text-[10px] font-bold">
                            <span class="text-gray-400">Terkumpul</span>
                            <span class="text-teal-600 dark:text-teal-400">Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($campaign->target_amount > 0)
                            @php
                                $pct = $campaign->percentage_achieved;
                            @endphp
                            <div class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="flex justify-between items-center text-[9px] text-gray-400">
                                <span>Target: Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                                <span>{{ $pct }}%</span>
                            </div>
                        @else
                            <div class="w-full h-1.5 bg-teal-100 dark:bg-teal-900 rounded-full overflow-hidden">
                                <div class="h-full bg-teal-500 w-full rounded-full"></div>
                            </div>
                            <span class="text-[9px] text-gray-400 block text-right font-medium">Program Abadi</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Donation Form (Right/Bottom) -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-8 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                        <h3 class="font-black text-lg text-gray-800 dark:text-white">Formulir Donasi</h3>
                        <p class="text-xs text-gray-400 mt-1">Lengkapi detail pembayaran donasi di bawah ini secara teliti.</p>
                    </div>

                    <form action="{{ route('portal.charity.donate.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                        @csrf
                        <input type="hidden" name="charity_campaign_id" value="{{ $campaign->id }}">

                        <!-- Nominal Amount -->
                        <div class="space-y-2">
                            <label for="amount" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Jumlah Donasi (Minimal Rp 1.000) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-black text-sm pointer-events-none">Rp</span>
                                <input type="number" name="amount" id="amount" x-model.number="amount" required min="1000" step="1000"
                                       class="block w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all"
                                       placeholder="Contoh: 10000">
                            </div>
                            @error('amount')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Anonymous Option -->
                        <div class="space-y-3">
                            <div class="flex items-center space-x-2.5">
                                <input type="checkbox" name="is_anonymous" id="is_anonymous" x-model="isAnonymous" value="1"
                                       class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500 w-4.5 h-4.5">
                                <label for="is_anonymous" class="text-xs font-bold text-gray-700 dark:text-gray-300 select-none">
                                    Donasi Anonim (Hamba Allah)
                                </label>
                            </div>
                        </div>

                        <!-- Donor Name -->
                        <div class="space-y-2" x-show="!isAnonymous">
                            <label for="donor_name" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Nama Donatur (Opsional)
                            </label>
                            <input type="text" name="donor_name" id="donor_name" :disabled="isAnonymous"
                                   value="{{ old('donor_name', auth()->user()->name) }}"
                                   class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all"
                                   placeholder="Contoh: Kemal Hafizh">
                            @error('donor_name')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method -->
                        <div class="space-y-3">
                            <label for="payment_method" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                Metode Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" x-model="paymentMethod" required
                                    class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all">
                                <option value="wadiah_wallet">Wadiah E-Wallet</option>
                                <option value="transfer">Transfer Bank YBWSA</option>
                                <option value="cash">Tunai ke Koperasi</option>
                            </select>
                            @error('payment_method')
                                <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Wadiah Wallet Box details & Student Limit Alert -->
                        <div class="p-5 rounded-2xl bg-teal-50/50 dark:bg-teal-950/20 border border-teal-100 dark:border-teal-900/50 space-y-2 transition-all"
                             x-show="paymentMethod === 'wadiah_wallet'">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-teal-800 dark:text-teal-300">Saldo Wadiah Anda</span>
                                <span class="text-xs font-black text-teal-600 dark:text-teal-400">
                                    Rp {{ number_format($walletBalance, 0, ',', '.') }}
                                </span>
                            </div>
                            
                            <!-- Alert: Amount > 10,000 for Students -->
                            <div x-show="isStudent && amount > 10000" 
                                 x-transition.opacity
                                 class="p-4 bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500 rounded-xl text-amber-800 dark:text-amber-300 text-xs font-bold space-y-1">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4.5 h-4.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <span>Pemberitahuan Limit Donasi Siswa</span>
                                </div>
                                <p class="font-medium text-[11px] text-amber-700 dark:text-amber-400 pl-6">
                                    Sesuai kebijakan sekolah, donasi harian siswa menggunakan e-wallet disarankan tidak melebihi Rp 10.000.
                                </p>
                            </div>
                        </div>

                        <!-- Bank Transfer Box details & upload input -->
                        <div class="p-5 rounded-2xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/50 space-y-4"
                             x-show="paymentMethod === 'transfer'">
                            <div class="text-xs font-semibold text-blue-800 dark:text-blue-300 space-y-1">
                                <p class="font-black uppercase tracking-wider text-[9px] text-blue-400">Rekening Tujuan</p>
                                <p class="text-sm font-bold text-gray-800 dark:text-white">Bank Syariah Indonesia (BSI)</p>
                                <p>Yayasan Badan Wakaf Sultan Agung (YBWSA)</p>
                                <p class="font-black text-gray-800 dark:text-white mt-1">No. Rekening: 123-456-7890</p>
                            </div>

                            <!-- File Evidence Upload -->
                            <div class="space-y-2">
                                <label for="evidence" class="block text-[10px] font-black uppercase tracking-widest text-blue-500">
                                    Unggah Bukti Transfer (Gambar, maks 2MB) <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="evidence" id="evidence" accept="image/*" :required="paymentMethod === 'transfer'"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:tracking-wider file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-all">
                                @error('evidence')
                                    <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Cash Box details -->
                        <div class="p-5 rounded-2xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50 text-xs font-semibold text-amber-800 dark:text-amber-300 space-y-1"
                             x-show="paymentMethod === 'cash'">
                            <p class="font-black uppercase tracking-wider text-[9px] text-amber-500">Instruksi Pembayaran Tunai</p>
                            <p class="text-sm font-bold text-gray-800 dark:text-white">Melalui Staf Tata Usaha (TU)</p>
                            <p class="font-medium text-gray-600 dark:text-gray-400 mt-1 leading-relaxed">
                                Silakan kunjungi ruang TU SMA Islam Sultan Agung 1 Semarang dan temui petugas administrasi keuangan untuk menyerahkan donasi tunai Anda dengan menunjukkan nomor resi setelah menekan tombol simpan.
                            </p>
                        </div>

                        <!-- Buttons -->
                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end items-center gap-4">
                            <a href="{{ route('portal.charity.portal') }}" 
                               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-bold text-sm transition-all">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-8 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-teal-600/20 transition-all duration-300 transform hover:-translate-y-0.5">
                                Kirim Donasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
