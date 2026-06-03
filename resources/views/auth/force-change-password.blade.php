<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <h2 class="text-xl font-black text-gray-800">Ganti Password Wajib</h2>
        <p class="text-sm text-gray-500 mt-2">Demi keamanan akun Anda, silakan buat password baru sebelum melanjutkan.</p>
    </div>

    @if(session('warning'))
        <div class="mb-4 p-3 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl text-sm font-medium">
            {{ session('warning') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.force-change.update') }}">
        @csrf

        <div class="mb-5">
            <label for="current_password" class="block font-medium text-sm text-gray-700 mb-1">Password Saat Ini</label>
            <input id="current_password" class="block w-full rounded-xl shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 py-3 px-4" type="password" name="current_password" required autofocus autocomplete="current-password" />
            @error('current_password')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>


        <div class="mb-5">
            <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Password Baru</label>
            <input id="password" class="block w-full rounded-xl shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 py-3 px-4" type="password" name="password" required autocomplete="new-password" />
            @error('password')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block font-medium text-sm text-gray-700 mb-1">Konfirmasi Password Baru</label>
            <input id="password_confirmation" class="block w-full rounded-xl shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 py-3 px-4" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <button type="submit" class="w-full py-3 bg-primary-600 text-white font-bold rounded-xl shadow-lg shadow-primary-900/10 hover:bg-primary-700 transition-all uppercase tracking-widest text-xs">
            Simpan Password Baru
        </button>
    </form>
</x-guest-layout>
