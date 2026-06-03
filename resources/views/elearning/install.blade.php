<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Pasang Aplikasi E-Learning') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12 max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-6 sm:p-10 shadow-xl border border-gray-100 dark:border-slate-700 relative overflow-hidden group">
            <!-- Glassmorphism Background Shapes -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all duration-700"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl group-hover:bg-primary-500/20 transition-all duration-700"></div>

            <div class="relative z-10 text-center space-y-6">
                <!-- App Icon Mockup -->
                <div class="mx-auto w-24 h-24 sm:w-28 sm:h-28 bg-emerald-600 dark:bg-emerald-700 rounded-3xl shadow-xl shadow-emerald-900/20 flex items-center justify-center text-white transform hover:scale-105 transition-transform duration-300">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-2xl sm:text-3xl font-black text-gray-900 dark:text-white tracking-tight">E-Learning SMA Terpadu</h3>
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 tracking-wider uppercase">Mobile App PWA Edition</p>
                </div>

                <p class="text-sm sm:text-base text-gray-600 dark:text-slate-300 max-w-lg mx-auto leading-relaxed">
                    Untuk kenyamanan belajar, kemudahan akses tugas, dan mengikuti ujian dengan sistem pengawasan integritas tinggi, silakan pasang aplikasi mandiri di perangkat seluler Anda.
                </p>

                <!-- Status Banner: Already Standalone -->
                <div id="already-installed" class="hidden bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 p-4 rounded-2xl max-w-md mx-auto">
                    <div class="flex items-center space-x-3 text-left">
                        <div class="flex-shrink-0 w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Aplikasi Sudah Terpasang!</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">Anda sedang menjalankan aplikasi dalam mode Standalone.</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('elearning.index') }}" class="inline-flex w-full justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                            Buka Dasbor Belajar
                        </a>
                    </div>
                </div>

                <!-- Installation Controllers -->
                <div id="install-actions" class="space-y-6 max-w-md mx-auto pt-4">
                    <!-- Standard Android/Chrome Prompt Button -->
                    <button id="btn-install-pwa" class="hidden w-full px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-bold shadow-lg shadow-emerald-900/20 transform hover:-translate-y-0.5 transition-all duration-150 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Pasang Aplikasi Secara Instan</span>
                    </button>

                    <!-- iOS Guide Card -->
                    <div id="ios-guide" class="hidden bg-slate-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-800 p-6 rounded-3xl text-left space-y-4 shadow-inner">
                        <div class="flex items-center space-x-2 text-amber-500">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                            <span class="text-xs font-extrabold uppercase tracking-wider">Petunjuk Khusus Perangkat iOS (iPhone/iPad)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Safari di iOS tidak mendukung tombol instalasi otomatis, silakan pasang secara manual dengan cara berikut:</p>
                        <ol class="space-y-3 text-xs text-gray-700 dark:text-slate-300 font-medium">
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full flex items-center justify-center mr-2 text-[10px] font-bold">1</span>
                                <span>Tekan tombol <strong>"Bagikan" (Share)</strong> <svg class="w-4 h-4 inline-block mx-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 10.742l5.253-2.627M8.684 13.258l5.253 2.627M8 12a3 3 0 11-6 0 3 3 0 016 0zm6-5a3 3 0 11-6 0 3 3 0 016 0zm6 10a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> di bilah navigasi bawah Safari.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full flex items-center justify-center mr-2 text-[10px] font-bold">2</span>
                                <span>Gulir ke bawah lalu pilih opsi <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-full flex items-center justify-center mr-2 text-[10px] font-bold">3</span>
                                <span>Beri nama aplikasi lalu ketuk <strong>"Tambah" (Add)</strong> di pojok kanan atas.</span>
                            </li>
                        </ol>
                    </div>

                    <!-- General Info Guide (Fallback) -->
                    <div id="general-info" class="bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-800 p-5 rounded-3xl text-left space-y-2">
                        <div class="flex items-center space-x-2 text-emerald-600 dark:text-emerald-400 text-xs font-bold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Mengapa Menggunakan PWA?</span>
                        </div>
                        <ul class="text-[11px] text-gray-500 dark:text-slate-400 list-disc list-inside space-y-1">
                            <li>Bisa diakses langsung dari home screen seperti aplikasi native.</li>
                            <li>Tampilan bersih tanpa bar alamat URL browser untuk fokus belajar penuh.</li>
                            <li>Mode Keamanan Ujian (Lock-down Mode) mewajibkan pengerjaan melalui aplikasi terpasang.</li>
                        </ul>
                    </div>

                    <!-- Skip Button to Dashboard -->
                    <div>
                        <a href="{{ route('elearning.index') }}" class="text-xs font-bold text-gray-400 dark:text-slate-500 hover:text-emerald-600 hover:underline transition-colors">
                            Lanjutkan ke Versi Web Sementara →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnInstall = document.getElementById('btn-install-pwa');
            const iosGuide = document.getElementById('ios-guide');
            const alreadyInstalled = document.getElementById('already-installed');
            const installActions = document.getElementById('install-actions');

            let deferredPrompt;

            // Check if already in standalone mode
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone;

            if (isStandalone) {
                alreadyInstalled.classList.remove('hidden');
                btnInstall.classList.add('hidden');
                iosGuide.classList.add('hidden');
                document.getElementById('general-info').classList.add('hidden');
            } else {
                // Check user agent to identify platform
                const ua = window.navigator.userAgent;
                const isiOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;

                if (isiOS) {
                    iosGuide.classList.remove('hidden');
                } else {
                    // Register beforeinstallprompt event for Android/Chrome/Edge
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        deferredPrompt = e;
                        btnInstall.classList.remove('hidden');
                    });
                }
            }

            btnInstall.addEventListener('click', async () => {
                if (!deferredPrompt) return;

                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                
                deferredPrompt = null;
                btnInstall.classList.add('hidden');
            });
        });
    </script>
</x-app-layout>
