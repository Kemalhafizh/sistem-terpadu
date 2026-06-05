<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold text-center uppercase tracking-wide" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPass: false }">
        @csrf

        <!-- Email Address -->
        <div class="group">
            <label for="email" class="block text-[10px] font-bold text-slate-450 uppercase tracking-widest mb-2 group-focus-within:text-emerald-600 transition-colors">Alamat Email Resmi</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                    </svg>
                </span>
                <input 
                    id="email" 
                    class="block w-full pl-10 pr-4 py-3.5 bg-slate-50/50 border border-slate-200/80 rounded-xl text-slate-800 text-sm font-medium placeholder-slate-400 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all duration-200 shadow-sm" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="nama@sultanagung1.sch.id"
                    required 
                    autofocus 
                    autocomplete="username" 
                />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-rose-600 font-semibold text-[10px] uppercase tracking-wider bg-rose-50 p-2.5 rounded-xl border border-rose-100/50" />
        </div>

        <!-- Password -->
        <div class="group">
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-[10px] font-bold text-slate-450 uppercase tracking-widest group-focus-within:text-emerald-600 transition-colors">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a class="text-[9px] font-bold text-emerald-650 hover:text-emerald-700 uppercase tracking-widest transition-colors" href="{{ route('password.request') }}">
                        Lupa Sandi?
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                </span>
                
                <input 
                    id="password" 
                    class="block w-full pl-10 pr-12 py-3.5 bg-slate-50/50 border border-slate-200/80 rounded-xl text-slate-800 text-sm font-medium placeholder-slate-400 focus:bg-white focus:border-emerald-600 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all duration-200 shadow-sm"
                    :type="showPass ? 'text' : 'password'"
                    name="password"
                    placeholder="••••••••"
                    required 
                    autocomplete="current-password" 
                />

                <!-- Password Visibility Toggle Button -->
                <button 
                    type="button" 
                    @click="showPass = !showPass" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
                >
                    <!-- Eye Open -->
                    <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <!-- Eye Closed -->
                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-rose-600 font-semibold text-[10px] uppercase tracking-wider bg-rose-50 p-2.5 rounded-xl border border-rose-100/50" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/10 cursor-pointer transition-all" 
                    name="remember"
                >
                <span class="ms-2.5 text-[10px] font-bold text-slate-450 uppercase tracking-widest group-hover:text-slate-700 transition-colors">Ingat Sesi Saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-1">
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 py-3.5 rounded-xl text-xs font-bold text-white uppercase tracking-[0.15em] shadow-md shadow-emerald-600/15 hover:shadow-lg hover:shadow-emerald-600/25 active:scale-98 transition-all duration-200 cursor-pointer">
                Masuk ke Sistem
            </button>
        </div>

        <!-- Separator -->
        <div class="relative flex items-center justify-center py-2">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-150"></div>
            </div>
            <div class="relative px-3 bg-white text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                atau masuk dengan
            </div>
        </div>

        <!-- Google SSO Button -->
        <div>
            <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-3 w-full bg-white hover:bg-slate-50/80 active:bg-slate-100 border border-slate-200/80 py-3.5 rounded-xl text-[10px] font-bold text-slate-700 uppercase tracking-[0.1em] shadow-sm hover:border-slate-300 active:scale-98 transition-all duration-200">
                <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                Masuk dengan Google
            </a>
        </div>
    </form>
</x-guest-layout>
