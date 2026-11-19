<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Reset kata sandi melalui OTP WhatsApp menggunakan nomor WhatsApp yang terdaftar pada akun Sulaone / Hanyakra Anda.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (! $whatsappAvailable)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
            <strong>Layanan OTP WhatsApp belum aktif.</strong>
            Hubungi Wali Kelas (untuk siswa) atau Tata Usaha sekolah untuk mendapatkan kata sandi sementara baru.
        </div>
    @else
        <form method="POST" action="{{ route('password.whatsapp.send') }}" class="space-y-4 border-b border-gray-200 pb-5 mb-5">
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
                <a href="{{ route('login') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                    {{ __('Kembali ke Login') }}
                </a>
                <x-primary-button>
                    {{ __('Simpan Kata Sandi Baru') }}
                </x-primary-button>
            </div>
        </form>
    @endif

    @if (! $whatsappAvailable)
        <div class="flex justify-end">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ __('Kembali ke Login') }}
            </a>
        </div>
    @endif
</x-guest-layout>
