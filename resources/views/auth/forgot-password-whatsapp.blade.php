<x-guest-layout>
    <div class="mb-4 text-sm text-slate-600 dark:text-slate-300">
        {{ __('Reset kata sandi melalui OTP WhatsApp menggunakan nomor WhatsApp yang terdaftar pada akun Sulaone / Hanyakra Anda.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (! $whatsappAvailable)
        <div class="mb-4 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/40 p-4 text-sm text-amber-800 dark:text-amber-200">
            <strong class="block mb-1">Layanan OTP WhatsApp belum aktif.</strong>
            Hubungi Wali Kelas (untuk siswa) atau Tata Usaha sekolah untuk mendapatkan kata sandi sementara baru.
        </div>
    @else
        <div class="border-b border-slate-200 dark:border-slate-700 pb-5 mb-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-3">
                {{ __('Langkah 1: Kirim Kode OTP ke WhatsApp') }}
            </h3>
            <form method="POST" action="{{ route('password.whatsapp.send') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="wa_identifier_req" :value="__('Email / No. HP / NIS / NISN / NIY')" />
                    <x-text-input id="wa_identifier_req" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" required autofocus />
                    <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button>
                        {{ __('Kirim Kode OTP ke WhatsApp') }}
                    </x-primary-button>
                </div>
            </form>
        </div>

        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-3">
                {{ __('Langkah 2: Verifikasi OTP & Buat Kata Sandi Baru') }}
            </h3>
            <form method="POST" action="{{ route('password.whatsapp.reset') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="identifier" :value="__('Email / No. HP / NIS / NISN / NIY')" />
                    <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" required />
                </div>

                <div>
                    <x-input-label for="otp" :value="__('Kode OTP WhatsApp (6 Digit)')" />
                    <x-text-input id="otp" class="block mt-1 w-full" type="text" name="otp" :value="old('otp')" required autocomplete="one-time-code" />
                    <x-input-error :messages="$errors->get('otp')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Kata Sandi Baru')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('login') }}" class="text-sm text-slate-600 dark:text-slate-300 underline hover:text-slate-900 dark:hover:text-white">
                        {{ __('Kembali ke Login') }}
                    </a>
                    <x-primary-button>
                        {{ __('Simpan Kata Sandi Baru') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    @endif

    @if (! $whatsappAvailable)
        <div class="flex justify-end">
            <a href="{{ route('login') }}" class="text-sm text-slate-600 dark:text-slate-300 underline hover:text-slate-900 dark:hover:text-white">
                {{ __('Kembali ke Login') }}
            </a>
        </div>
    @endif
</x-guest-layout>
