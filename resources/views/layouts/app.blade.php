<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {!! app()->getLocale() === 'ar' ? 'dir="rtl"' : 'dir="ltr"' !!}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-theme" content="{{ auth()->check() ? auth()->user()->preferred_theme : 'system' }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ $appearance->app_name }}</title>

    <!-- Favicon -->
    <link class="favicon" rel="icon" type="image/png" href="{{ $appearance->favicon_path ? asset('storage/' . $appearance->favicon_path) : asset('images/logo-kotak.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family={{ str_replace(' ', '+', $appearance->font_family) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif

    <!-- Dynamic Appearance Branding Styles -->
    <style>
        :root {
            --color-primary-500: {{ $appearance->primary_color }} !important;
            --color-primary-600: {{ $appearance->secondary_color }} !important;
            --color-primary-700: {{ $appearance->secondary_color }} !important;
            --color-primary-800: {{ $appearance->sidebar_color }} !important;
            --color-primary-900: {{ $appearance->sidebar_color }} !important;
            --color-primary-950: {{ $appearance->sidebar_color }} !important;
            
            @if(app()->getLocale() === 'ar')
                --font-sans: 'Cairo', 'Amiri', ui-sans-serif, system-ui, sans-serif !important;
            @else
                --font-sans: '{{ $appearance->font_family }}', 'Inter', ui-sans-serif, system-ui, sans-serif !important;
            @endif
        }
        
        body {
            font-family: var(--font-sans) !important;
        }

        [dir="rtl"] {
            text-align: right;
        }
        [dir="rtl"] .dropdown-menu-end {
            right: auto !important;
            left: 0 !important;
        }
        [dir="rtl"] select {
            background-position: left 0.5rem center !important;
            padding-left: 2rem !important;
            padding-right: 0.75rem !important;
        }
    </style>

    @if($appearance->custom_css)
        @php
            $__custom_css = trim($appearance->custom_css);
            if (strlen($__custom_css) > 0) {
                if (strpos($__custom_css, '<' . 'style') === 0) {
                    echo $__custom_css;
                } else {
                    $styleStart = '<' . 'style' . '>';
                    $styleEnd = '<' . '/' . 'style' . '>';
                    echo $styleStart . $__custom_css . $styleEnd;
                }
            }
        @endphp
    @endif

    <!-- Theme Manager -->
    <script>
        const dbTheme = document.querySelector('meta[name="user-theme"]')?.getAttribute('content') || 'system';
        if (dbTheme !== 'system') {
            localStorage.setItem('theme', dbTheme);
        }

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
            const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.getAttribute('content') === 'true';
            if (isAuthenticated) {
                fetch('/preferences/theme/' + themeValue, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                });
            }

            window.dispatchEvent(new CustomEvent('theme-changed', { detail: isDark }));
        }
        
        applyTheme();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- PWA Manifest & Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d9488">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/images/logo-kotak.png">

    <!-- Register Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .catch(err => console.warn('ServiceWorker registration failed:', err));
            });
        }
    </script>
    
    <!-- Offline Queue & Sync Manager -->
    <script src="/offline-queue.js" defer></script>

    {{-- Page-level assets: <x-slot name="styles"> and @push('styles') --}}
    {{ $styles ?? '' }}
    @stack('styles')
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
    @submit.window="if (!$event.defaultPrevented && $event.target.tagName === 'FORM' && $event.target.target !== '_blank' && !$event.target.hasAttribute('data-no-loader') && !($event.target.dataset.offlineSync === 'true' && !navigator.onLine)) loading = true"
    @pageshow.window="loading = false"
>
    @include('partials.pwa-feedback')

    <!-- Skip Navigation Link (Accessibility) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:start-4 focus:z-300 focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-xl focus:shadow-xl focus:text-sm focus:font-bold focus:outline-none"
    >
        Langsung ke konten utama
    </a>

    <!-- Global Loading Overlay -->
    <div x-show="loading" class="fixed inset-0 z-100 flex items-center justify-center bg-white/50 dark:bg-gray-900/50 backdrop-blur-sm transition-opacity" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
        <div class="relative">
            <div class="w-12 h-12 border-4 border-primary-200 dark:border-primary-900 rounded-full animate-spin border-t-primary-600"></div>
            <div class="absolute inset-0 flex items-center justify-center">
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

    {{-- Impersonation Banner --}}
    @if(session()->has('impersonate_by'))
        <div class="bg-red-600 text-white p-3 text-center text-xs font-black uppercase tracking-widest z-50 flex items-center justify-center gap-4">
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
                    <div class="w-full py-5 px-4 sm:px-6 lg:px-8 text-gray-800 dark:text-gray-100">
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
            <main id="main-content" class="flex-1 overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50 p-4 md:p-6 lg:p-10 transition-all duration-300 page-enter">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

    </div>

    <!-- KaTeX LaTeX Math & Chemical Equation Renderer for Saintek -->
    <x-vendor.style lib="katex" />
    <x-vendor.script lib="katex" defer />
    <x-vendor.script lib="katex-mhchem" defer />
    <x-vendor.script lib="katex-auto-render" defer onload="renderSistaLatex()" />
    <script>
        function renderSistaLatex(element = document.body) {
            if (typeof renderMathInElement === 'function') {
                try {
                    renderMathInElement(element, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '\\[', right: '\\]', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false}
                        ],
                        throwOnError: false
                    });
                } catch (e) {
                    console.error("KaTeX Auto-render Error:", e);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            renderSistaLatex();
        });
        window.addEventListener('render-latex', function() {
            renderSistaLatex();
        });
    </script>

    {{-- Page-level scripts: <x-slot name="scripts"> and @push('scripts') --}}
    {{ $scripts ?? '' }}
    @stack('scripts')
</body>
</html>
