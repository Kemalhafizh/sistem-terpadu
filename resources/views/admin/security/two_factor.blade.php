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
                    TOTP 2FA & Session Hijacking Safeguard
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penguat identitas autentikasi dua faktor (TOTP), pengikatan sesi Subnet IP, dan Step-Up elevation safeguard.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Status Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- 2FA Status -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status TOTP 2FA</span>
                    <div class="mt-3">
                        @if($is2faEnabled)
                            <span class="px-3.5 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                AKTIF (PROTECTED)
                            </span>
                        @else
                            <span class="px-3.5 py-1.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                NON-AKTIF (RECOMMENDED)
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Session Binding Status -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Session Fingerprint Safeguard</span>
                    <div class="mt-3">
                        <span class="px-3.5 py-1.5 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            SUBNET IP BINDING ACTIVE
                        </span>
                    </div>
                </div>

                <!-- Step-Up Elevation Status -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status Step-Up Elevation</span>
                    <div class="mt-3">
                        @if($isElevated)
                            <span class="px-3.5 py-1.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-2">
                                ELEVATED (15 MINS)
                            </span>
                        @else
                            <span class="px-3.5 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-2">
                                STANDARD SESSION
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if(! $is2faEnabled)
                <!-- 2FA Activation Setup Wizard -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Aktivasi Autentikasi Dua Faktor (TOTP 2FA)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pindai kode QR atau salin kunci rahasia ke aplikasi Authenticator (Google Authenticator, Authy, Microsoft Authenticator).</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kunci Rahasia (Secret Key)</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="{{ $secret }}" class="w-full bg-slate-100 dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 font-mono text-sm font-black px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                </div>
                            </div>

                            <div class="p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs space-y-2 text-gray-600 dark:text-gray-300">
                                <p class="font-bold">Panduan Aktivasi:</p>
                                <ol class="list-decimal list-inside space-y-1 text-[11px]">
                                    <li>Buka aplikasi Authenticator di smartphone Anda.</li>
                                    <li>Pilih "Tambah Akun" lalu "Pindai Kode QR" atau masukkan Kunci Rahasia secara manual.</li>
                                    <li>Masukkan 6-digit kode OTP yang dihasilkan aplikasi di samping untuk konfirmasi.</li>
                                </ol>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <form action="{{ route('admin.security.2fa.confirm') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1.5">Masukkan 6-Digit Kode Authenticator</label>
                                    <input type="text" name="code" maxlength="6" required placeholder="123456" class="w-full text-center font-mono text-2xl font-black tracking-[0.5em] bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 py-3 focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/30 transition-all">
                                    Aktifkan Verifikasi 2FA
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- Active 2FA Enabled State & Recovery Codes -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">Kode Pemulihan Darurat (Emergency Recovery Codes)</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Simpan kode pemulihan ini di tempat aman. Kode ini dapat digunakan jika smartphone Anda hilang.</p>
                        </div>

                        <form action="{{ route('admin.security.2fa.disable') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA?')">
                            @csrf
                            <input type="password" name="password" required placeholder="Kata Sandi Konfirmasi" class="text-xs bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-1.5 mr-2">
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                Nonaktifkan 2FA
                            </button>
                        </form>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 font-mono text-xs">
                        @foreach($user->totp_recovery_codes ?? [] as $code)
                            <div class="p-3 bg-slate-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-center font-bold text-gray-800 dark:text-gray-200">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Active Session & Fingerprint Safeguard Info -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-4">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Informasi Sesi Aktif & Fingerprint Safeguard</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sistem mengikat sesi Anda dengan kombinasi Subnet IP dan Perangkat User-Agent untuk mencegah Session Hijacking.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-gray-400 block text-[10px] uppercase font-bold">Alamat IP Sesi</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $activeSession['ip'] }}</span>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-gray-400 block text-[10px] uppercase font-bold">SHA-256 Session Fingerprint</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 truncate block" title="{{ $activeSession['fingerprint'] }}">
                            {{ Str::limit($activeSession['fingerprint'], 32) }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
