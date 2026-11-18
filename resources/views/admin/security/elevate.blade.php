<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
            <span class="p-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-2xl border border-rose-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </span>
            Konfirmasi Keamanan Tingkat Tinggi (Step-Up Elevation)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto px-4">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6">
                <div class="text-center space-y-2">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Tindakan Memerlukan Verifikasi Ulang</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Silakan masukkan Kata Sandi Anda (dan Kode 2FA jika aktif) untuk menyetujui akses tindakan berdampak tinggi ini selama 15 menit.</p>
                </div>

                @if(session('warning'))
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-500/30 text-amber-700 dark:text-amber-300 rounded-xl text-xs font-bold text-center">
                        {{ session('warning') }}
                    </div>
                @endif

                <form action="{{ route('admin.security.2fa.elevate') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kata Sandi Akun</label>
                        <input type="password" name="password" required autofocus class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold focus:ring-2 focus:ring-rose-500">
                        @error('password')
                            <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    @if(auth()->user()->hasTotpEnabled())
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Kode 6-Digit TOTP Authenticator</label>
                            <input type="text" name="totp_code" maxlength="8" placeholder="123456" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white font-mono text-center text-lg font-black tracking-widest rounded-xl border border-gray-300 dark:border-gray-700 py-2.5 focus:ring-2 focus:ring-rose-500">
                            @error('totp_code')
                                <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-rose-600/30 transition-all">
                        Verifikasi & Lanjutkan Akses
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
