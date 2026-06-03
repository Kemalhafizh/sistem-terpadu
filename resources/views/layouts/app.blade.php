<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {!! app()->getLocale() === 'ar' ? 'dir="rtl"' : '' !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ $appearance->app_name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $appearance->favicon_path ? asset('storage/' . $appearance->favicon_path) : asset('images/logo-kotak.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family={{ str_replace(' ', '+', $appearance->font_family) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dynamic Appearance Branding Styles -->
    <style>
        :root {
            --color-primary-500: {{ $appearance->primary_color }} !important;
            --color-primary-600: {{ $appearance->secondary_color }} !important;
            --color-primary-700: {{ $appearance->secondary_color }} !important;
            --color-primary-800: {{ $appearance->sidebar_color }} !important;
            --color-primary-900: {{ $appearance->sidebar_color }} !important;
            --color-primary-950: {{ $appearance->sidebar_color }} !important;
            
            --font-sans: '{{ $appearance->font_family }}', 'Inter', ui-sans-serif, system-ui, sans-serif !important;
        }
        
        body {
            font-family: var(--font-sans) !important;
        }

        /* Custom Admin CSS Override */
        {!! $appearance->custom_css !!}
    </style>

    <!-- Theme Manager -->
    <script>
        @auth
            const dbTheme = '{{ auth()->user()->preferred_theme }}';
            if (dbTheme !== 'system') {
                localStorage.setItem('theme', dbTheme);
            }
        @endauth

        function applyTheme() {
            if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            const themeValue = isDark ? 'dark' : 'light';
            localStorage.setItem('theme', themeValue);
            
            // Sync with backend if authenticated
            @auth
                fetch('/preferences/theme/' + themeValue, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            @endauth

            window.dispatchEvent(new CustomEvent('theme-changed', { detail: isDark }));
        }
        
        applyTheme();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- PWA Manifest & Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/images/logo-kotak.png">

    <!-- Register Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(reg => console.log('ServiceWorker registered successfully.', reg.scope))
                    .catch(err => console.log('ServiceWorker registration failed: ', err));
            });
        }
    </script>
</head>
<body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-900 flex h-screen overflow-hidden flex-col" 
    x-data="{ 
        sidebarOpen: false, 
        sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        loading: false,
        isOnline: navigator.onLine,
        showOfflineToast: !navigator.onLine,
        showOnlineBackToast: false,
        pwaDeferredPrompt: null,
        showPwaBanner: false,
        toggleSidebarCollapse() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
        }
    }"
    x-init="
        window.addEventListener('online', () => { 
            isOnline = true; 
            showOfflineToast = false;
            showOnlineBackToast = true;
            setTimeout(() => { showOnlineBackToast = false; }, 3000);
        });
        window.addEventListener('offline', () => { 
            isOnline = false; 
            showOfflineToast = true;
            showOnlineBackToast = false;
        });
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            pwaDeferredPrompt = e;
            if (localStorage.getItem('pwa_dismissed') !== 'true') {
                showPwaBanner = true;
            }
        });
        window.addEventListener('appinstalled', () => {
            pwaDeferredPrompt = null;
            showPwaBanner = false;
            localStorage.setItem('pwa_dismissed', 'true');
        });
    "
    @submit.window="loading = true"
    @beforeunload.window="loading = true"
>
    <!-- Connection Guard: Glassmorphic Offline Toast -->
    <div x-show="showOfflineToast"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-10 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-10 opacity-0 scale-95"
         class="fixed bottom-6 right-6 z-[300] bg-red-950/85 dark:bg-red-950/95 text-red-200 border border-red-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center space-x-4 max-w-sm"
         style="display: none;">
        <div class="relative flex-shrink-0 flex items-center justify-center w-10 h-10 bg-red-900/50 rounded-xl text-red-400">
            <span class="absolute inline-flex h-3 w-3 rounded-full bg-red-500 animate-ping"></span>
            <svg class="w-5 h-5 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path></svg>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-black tracking-tight text-white">Koneksi Terputus</h4>
            <p class="text-[11px] font-semibold text-red-300 mt-0.5">Mode luring aktif. Beberapa aksi mungkin ditunda.</p>
        </div>
        <button @click="isOnline = navigator.onLine; if(isOnline) { showOfflineToast = false; showOnlineBackToast = true; setTimeout(() => { showOnlineBackToast = false; }, 3000); }" class="flex-shrink-0 px-3 py-1.5 bg-red-800/60 hover:bg-red-700/60 text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-red-700/40">
            Coba Lagi
        </button>
    </div>

    <!-- Connection Guard: Glassmorphic Online Restored Toast -->
    <div x-show="showOnlineBackToast"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-10 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-300 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-[-20px] opacity-0 scale-95"
         class="fixed bottom-6 right-6 z-[300] bg-emerald-950/85 dark:bg-emerald-950/95 text-emerald-200 border border-emerald-800/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex items-center space-x-4 max-w-sm"
         style="display: none;">
        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 bg-emerald-900/50 rounded-xl text-emerald-400">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="flex-grow">
            <h4 class="text-sm font-black tracking-tight text-white">Koneksi Pulih!</h4>
            <p class="text-[11px] font-semibold text-emerald-300 mt-0.5">Sinkronisasi data berhasil diselesaikan.</p>
        </div>
    </div>

    <!-- PWA Install Banner: Glassmorphic floating banner -->
    <div x-show="showPwaBanner"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-10 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-10 opacity-0 scale-95"
         class="fixed bottom-6 left-6 z-[300] bg-slate-900/85 dark:bg-slate-950/95 text-slate-100 border border-slate-700/50 backdrop-blur-md rounded-2xl p-4 shadow-2xl flex flex-col md:flex-row items-start md:items-center space-y-3 md:space-y-0 md:space-x-4 max-w-md w-full"
         style="display: none;">
        <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-400">
            <img src="/images/logo-kotak.png" alt="Logo SISTA" class="w-8 h-8 rounded-lg object-contain">
        </div>
        <div class="flex-grow">
            <h4 class="text-sm font-bold tracking-tight text-white">Pasang Aplikasi SISTA</h4>
            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">Nikmati akses instan lebih cepat, hemat kuota, dan dukungan moda luring offline penuh.</p>
        </div>
        <div class="flex-shrink-0 flex items-center space-x-2 w-full md:w-auto justify-end">
            <button @click="showPwaBanner = false; localStorage.setItem('pwa_dismissed', 'true')" class="px-3 py-1.5 hover:bg-slate-800 text-slate-400 hover:text-white rounded-lg text-xs font-semibold transition-all">
                Nanti
            </button>
            <button @click="
                pwaDeferredPrompt.prompt();
                pwaDeferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    localStorage.setItem('pwa_dismissed', 'true');
                    showPwaBanner = false;
                    pwaDeferredPrompt = null;
                });
            " class="px-4 py-1.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-xs font-bold shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all border border-blue-400/20">
                Pasang
            </button>
        </div>
    </div>

    <!-- Skip Navigation Link (Accessibility) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[300] focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-xl focus:shadow-xl focus:text-sm focus:font-bold focus:outline-none"
    >
        Langsung ke konten utama
    </a>

    <!-- Global Loading Overlay -->
    <div x-show="loading" class="fixed inset-0 z-[100] flex items-center justify-center bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm transition-opacity" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
        <div class="relative">
            <div class="w-12 h-12 border-4 border-primary-200 dark:border-primary-900 rounded-full animate-spin border-t-primary-600"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-2 h-2 bg-primary-600 rounded-full animate-ping"></div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    {{-- Impersonation Banner --}}
    @if(session()->has('impersonate_by'))
        <div class="bg-red-600 text-white p-3 text-center text-xs font-black uppercase tracking-widest z-50 flex items-center justify-center space-x-4">
            <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            <span>MAINTENANCE MODE: ANDA SEDANG MENYAMAR SEBAGAI {{ auth()->user()->name }}</span>
            <a href="{{ route('admin.impersonate.leave') }}" class="px-4 py-1 bg-white text-red-600 rounded-lg hover:bg-gray-100 transition-all font-bold">KEMBALI KE ADMIN</a>
        </div>
    @endif

    <div class="flex flex-1 overflow-hidden">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" class="fixed inset-0 z-20 bg-gray-900/50 backdrop-blur-sm md:hidden" @click="sidebarOpen = false" x-transition.opacity></div>

        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden align-top relative">
            <!-- Top Navigation -->
            @include('layouts.navigation')

            <!-- Page Heading with Breadcrumb -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow border-b border-gray-200 dark:border-gray-700 z-10 relative">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8 text-gray-800 dark:text-gray-100">
                        @isset($breadcrumbs)
                            <div class="mb-3">
                                {{ $breadcrumbs }}
                            </div>
                        @endisset
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main id="main-content" class="flex-1 overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50 p-4 md:p-6 lg:p-10 transition-all duration-300">
                {{ $slot }}
            </main>
        </div>
        <!-- Chatbot Floating Widget -->
        <x-chatbot-widget />
    </div>
</body>
</html>
