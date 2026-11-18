<!-- Connection Guard: Offline Toast -->
<div x-show="showOfflineToast"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="translate-y-10 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="translate-y-10 opacity-0 scale-95"
     class="fixed bottom-6 end-6 z-300 bg-red-950/90 dark:bg-red-950/95 text-red-200 border border-red-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center gap-4 max-w-sm"
     style="display: none;">
    <div class="relative shrink-0 flex items-center justify-center w-10 h-10 bg-red-900/50 rounded-xl text-red-400">
        <span class="absolute inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
        <svg class="w-5 h-5 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path></svg>
    </div>
    <div class="flex-1 min-w-0">
        <h4 class="text-sm font-black tracking-tight text-white">Koneksi Terputus</h4>
        <p class="text-[11px] font-semibold text-red-300 mt-0.5">Mode luring aktif. Beberapa aksi mungkin ditunda.</p>
    </div>
    <button @click="isOnline = navigator.onLine; if(isOnline) { showOfflineToast = false; showOnlineBackToast = true; setTimeout(() => { showOnlineBackToast = false; }, 3000); }" class="shrink-0 px-3 py-1.5 bg-red-800/60 hover:bg-red-700/60 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-red-700/40">
        Coba Lagi
    </button>
</div>

<!-- Connection Guard: Online Restored Toast -->
<div x-show="showOnlineBackToast"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="translate-y-10 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-300 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="translate-y-[-20px] opacity-0 scale-95"
     class="fixed bottom-6 end-6 z-300 bg-emerald-950/90 dark:bg-emerald-950/95 text-emerald-200 border border-emerald-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center gap-4 max-w-sm"
     style="display: none;">
    <div class="shrink-0 flex items-center justify-center w-10 h-10 bg-emerald-900/50 rounded-xl text-emerald-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
    </div>
    <div class="grow min-w-0">
        <h4 class="text-sm font-black tracking-tight text-white">Koneksi Pulih</h4>
        <p class="text-[11px] font-semibold text-emerald-300 mt-0.5">Terhubung kembali ke jaringan.</p>
    </div>
</div>

<!-- PWA Install Banner -->
<div x-show="showPwaBanner"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="translate-y-10 opacity-0 scale-95"
     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
     x-transition:leave-end="translate-y-10 opacity-0 scale-95"
     class="fixed bottom-6 inset-x-6 sm:inset-x-auto sm:start-6 z-300 bg-slate-900/90 dark:bg-slate-950/95 text-slate-100 border border-slate-700/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex flex-col md:flex-row items-start md:items-center gap-3 md:gap-4 max-w-md sm:w-full"
     style="display: none;">
    <div class="shrink-0 flex items-center justify-center w-12 h-12 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
        <img src="/images/logo-kotak.png" alt="Logo Sulaone" class="w-8 h-8 rounded-lg object-contain">
    </div>
    <div class="grow min-w-0">
        <h4 class="text-sm font-bold tracking-tight text-white">Pasang Aplikasi Sulaone</h4>
        <p class="text-[11px] text-slate-300 mt-0.5 leading-relaxed">Akses portal sekolah lebih cepat langsung dari layar utama perangkat Anda.</p>
    </div>
    <div class="shrink-0 flex items-center gap-2 w-full md:w-auto justify-end">
        <button @click="showPwaBanner = false; localStorage.setItem('pwa_dismissed', 'true')" class="px-3 py-1.5 hover:bg-slate-800 text-slate-300 hover:text-white rounded-lg text-xs font-semibold transition-all">
            Nanti
        </button>
        <button @click="
            if (!pwaDeferredPrompt) return;
            pwaDeferredPrompt.prompt();
            pwaDeferredPrompt.userChoice.then(() => {
                localStorage.setItem('pwa_dismissed', 'true');
                showPwaBanner = false;
                pwaDeferredPrompt = null;
            });
        " class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-lg shadow-emerald-900/20 transition-all border border-emerald-400/20">
            Pasang
        </button>
    </div>
</div>
