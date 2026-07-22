<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    PDPL Privacy & Data Anonymization Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kepatuhan UU No. 27 Tahun 2022 (Perhitungan & Pelindungan Data Pribadi - PDP Indonesia).</p>
            </div>

            <div>
                <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-xl font-black text-xs border border-emerald-500/20 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    COMPLIANT (UU NO. 27/2022 PDP)
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Compliance Policy Overview Banner -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Masking PII Otomatis</span>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">Pseudonimisasi Data NIK & Kontak</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Penyambaran NIK, nomor telepon, dan email untuk ekspor analitik agar terhindar dari kebocoran identitas.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Right to be Forgotten</span>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">Protokol Penghapusan Identitas</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Anonimisasi data identitas alumni/siswa keluar tanpa merusak integritas riwayat nilai & presensi sekolah.</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-2">
                    <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Retensi Data PDP</span>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">Retensi 5 Tahun Regulasi</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">Pemberlakuan masa simpan data PII sesuai batas hukum nasional sebelum dianonimkan permanen.</p>
                </div>
            </div>

            <!-- PII Masking Tester & Right to be Forgotten Executor Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Interactive PII Masking Simulator -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Simulator Masking Data PII (Personally Identifiable Information)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Uji coba aturan penyambaran NIK, nomor HP, dan email sesuai regulasi PDP.</p>
                    </div>

                    <form action="{{ route('admin.security.privacy.preview') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" value="{{ session('preview_result.original_nik') ?? '3374012304950001' }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ session('preview_result.original_phone') ?? '081234567890' }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                            <input type="email" name="email" value="{{ session('preview_result.original_email') ?? 'kemalhafizh@gmail.com' }}" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono">
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                            Proses Masking PII
                        </button>
                    </form>

                    @if(session('preview_result'))
                        <div class="p-4 bg-indigo-500/10 rounded-2xl border border-indigo-500/20 space-y-2 text-xs font-mono">
                            <span class="text-indigo-400 font-bold block uppercase text-[10px]">HASIL ANONIMISASI PII:</span>
                            <div class="flex justify-between"><span>NIK:</span> <strong class="text-indigo-500">{{ session('preview_result.masked_nik') }}</strong></div>
                            <div class="flex justify-between"><span>Phone:</span> <strong class="text-indigo-500">{{ session('preview_result.masked_phone') }}</strong></div>
                            <div class="flex justify-between"><span>Email:</span> <strong class="text-indigo-500">{{ session('preview_result.masked_email') }}</strong></div>
                        </div>
                    @endif
                </div>

                <!-- Right: Right to be Forgotten Protocol Executor -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider">Eksekutor Protokol Hak untuk Dilupakan (Right to be Forgotten)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Memproses permintaan anonimisasi permanen data pribadi pengguna sesuai permintaan hukum PDP.</p>
                    </div>

                    <form action="{{ route('admin.security.privacy.right-to-be-forgotten') }}" method="POST" onsubmit="return confirm('Peringatan Hukum! Anda akan menganonimkan permanen identitas pengguna ini. Lanjutkan?')" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">ID Pengguna (User ID Target)</label>
                            <input type="number" name="user_id" placeholder="Masukkan ID User (misal: 10)" required class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-mono">
                        </div>

                        <div class="p-4 bg-rose-500/10 rounded-2xl border border-rose-500/20 text-xs text-rose-600 dark:text-rose-400 space-y-1">
                            <strong class="block font-bold">Dampak Eksekusi:</strong>
                            <ul class="list-disc list-inside text-[11px] space-y-0.5">
                                <li>Nama diubah menjadi <code class="font-mono">ANONYMIZED_USER_#ID</code></li>
                                <li>Email diubah menjadi <code class="font-mono">anonymized_#ID@privacy.local</code></li>
                                <li>Catatan audit kriptografi dibuat secara otomatis</li>
                            </ul>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                            Eksekusi Right to be Forgotten
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
