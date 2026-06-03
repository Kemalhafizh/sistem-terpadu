<x-guest-layout>
    <div class="mb-8 text-center">
        <div class="w-16 h-16 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-primary-100">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <h2 class="text-2xl font-black text-gray-800 tracking-tight">Verifikasi Dua Langkah</h2>
        <p class="text-xs text-gray-500 mt-2">Kami telah mengirimkan kode 6-digit ke email Anda.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 text-xs font-bold text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-100 animate-fade-in text-center">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verify-2fa.store') }}" class="space-y-6">
        @csrf

        <div>
            <x-input-label for="two_factor_code" :value="__('Kode Verifikasi')" class="text-[10px] font-black uppercase tracking-widest text-gray-400" />
            <x-text-input id="two_factor_code" class="block mt-1 w-full text-center text-2xl font-black tracking-[0.5em] bg-gray-50 border-gray-100 focus:ring-primary-500 rounded-2xl" type="text" name="two_factor_code" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('two_factor_code')" class="mt-2" />
        </div>

        <div class="flex flex-col space-y-3">
            <x-primary-button class="w-full justify-center py-4 bg-gray-900 hover:bg-gray-800 rounded-2xl shadow-xl shadow-gray-200 text-xs font-black transition-all">
                {{ __('VERIFIKASI SEKARANG') }}
            </x-primary-button>
            
            <a href="{{ route('verify-2fa.resend') }}" class="text-[10px] font-black text-primary-600 hover:text-primary-700 text-center uppercase tracking-widest transition-colors">
                Kirim ulang kode verifikasi
            </a>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-[10px] font-black text-gray-400 hover:text-rose-500 uppercase tracking-widest transition-colors">
                Batal & Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
