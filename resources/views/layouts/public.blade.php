<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  @yield('seo_meta')

  <!-- SEO Meta Tags & Open Graph -->
  <meta name="keywords" content="sulaone, sista, {{ mb_strtolower(school_name()) }}, sekolah islam, portal sekolah, spmb online">
  <meta name="author" content="{{ school_name() }}">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:site_name" content="Sulaone — SISTA {{ school_short_name() }}">
  <meta property="og:image" content="{{ asset('images/logo-kotak.png') }}">
  <meta property="twitter:card" content="summary">
  <meta property="twitter:image" content="{{ asset('images/logo-kotak.png') }}">

  <!-- PWA & Favicon Set -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0d9488">

  <!-- Structured Data (JSON-LD) -->
  <script type="application/ld+json">
  @verbatim
  {
   "@context": "https://schema.org",
   "@type": "School",
   @endverbatim
   "name": @json(school_name()),
  @verbatim
   "address": {
    "@type": "PostalAddress",
    "streetAddress": "Jl. Mataram No.657, Wonodri",
    "addressLocality": "Semarang",
    "addressRegion": "Jawa Tengah",
    "postalCode": "50242",
    "addressCountry": "ID"
   },
   @endverbatim
   "telephone": @json(school_phone()),
   "email": @json(school_email())
  }
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  @else
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  @endif

  <!-- Vite -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <!-- Register Service Worker -->
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .catch(err => console.warn('ServiceWorker registration failed:', err));
      });
    }
  </script>
  <script src="/offline-queue.js" defer></script>

  <style>
    body { 
      font-family: {{ app()->getLocale() === 'ar' ? "'Cairo', 'Amiri', sans-serif" : "'Inter', sans-serif" }}; 
      background-color: #f8fafc;
    }
    
    /* Surface Utilities */
    .glass {
      background: rgba(255, 255, 255, 0.72);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .glass-dark {
      background: rgba(15, 23, 42, 0.85);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }

    @@keyframes fade-in-up {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.6s ease-out forwards; }
  </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden selection:bg-primary-500 selection:text-white" 
  x-data="{ 
    scrolled: false,
    mobileMenuOpen: false,
    isOnline: navigator.onLine,
    showOfflineToast: !navigator.onLine,
    showOnlineBackToast: false,
    pwaDeferredPrompt: null,
    showPwaBanner: false
  }" 
  @scroll.window="scrolled = (window.pageYOffset > 20)"
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
>

  <!-- Navigation -->
  <nav :class="{'py-3 bg-white/95 backdrop-blur-md shadow-lg border-b border-slate-200': scrolled || mobileMenuOpen, 'py-5 bg-white/75 backdrop-blur-sm border-b border-slate-100/60': !scrolled && !mobileMenuOpen}" 
     class="fixed top-0 w-full z-50 transition-all duration-300">
    <div class="w-full px-6 flex justify-between items-center">
      <div class="flex items-center gap-3">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
          <div class="h-12 w-auto flex items-center justify-center p-1.5 rounded-xl bg-white shadow-sm border border-slate-100">
            <img src="{{ asset('images/logo-lonjong.png') }}" alt="{{ school_short_name() }}" class="h-full object-contain" 
               onerror="this.parentElement.innerHTML = '<span class=\'text-primary-700 font-bold text-sm px-2\'>Sulaone</span>'">
          </div>
          <div class="hidden sm:block">
            <h1 class="text-sm font-black text-slate-900 leading-tight">Sulaone<br><span class="text-[10px] font-bold text-primary-600 tracking-wider uppercase">SISTA • {{ school_short_name() }}</span></h1>
          </div>
        </a>
      </div>

      <div class="flex items-center gap-4 md:gap-8">
        <div class="hidden lg:flex items-center gap-6">
          <a href="{{ route('home') }}#fitur" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_features') }}</a>
          <!-- Profil Dropdown -->
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors flex items-center gap-1">
              <span>{{ __('public.nav_profile') }}</span>
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" 
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="transform opacity-0 scale-95"
               x-transition:enter-end="transform opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="transform opacity-100 scale-100"
               x-transition:leave-end="transform opacity-0 scale-95"
               class="absolute start-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 z-50 overflow-hidden py-1"
               style="display: none;">
              <a href="{{ route('public.about') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_about') }}</a>
              <a href="{{ route('public.facilities') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_facilities') }}</a>
              <a href="{{ route('public.achievements') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_achievements') }}</a>
              <a href="{{ route('public.staff-directory') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_staff') }}</a>
              <a href="{{ route('public.virtual-tour') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_virtual_tour') }}</a>
              <a href="{{ route('public.contact') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary-600 transition-colors">{{ __('public.nav_contact') }}</a>
            </div>
          </div>
          <a href="{{ route('public.posts') }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_news') }}</a>
          <a href="{{ route('public.gallery') }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_gallery') }}</a>
          <a href="{{ route('home') }}#spmb" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_spmb') }}</a>
          @if(isset($navPages))
            @foreach($navPages as $navPage)
              <a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ $navPage->title }}</a>
            @endforeach
          @endif
        </div>
        <div class="flex items-center gap-3">
          <!-- Language Selector Dropdown -->
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/60 border border-slate-200 hover:bg-white transition-all text-xs font-bold uppercase tracking-wider text-slate-700">
              <span>{{ app()->getLocale() === 'id' ? 'ID' : (app()->getLocale() === 'en' ? 'EN' : 'AR') }}</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" @click.away="open = false" 
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="transform opacity-0 scale-95"
               x-transition:enter-end="transform opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="transform opacity-100 scale-100"
               x-transition:leave-end="transform opacity-0 scale-95"
               class="absolute end-0 mt-2 w-36 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 z-50 overflow-hidden" 
               style="display:none">
              <div class="p-1.5 space-y-1">
                <form action="{{ route('preferences.locale', 'id') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'id' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Indonesia</span>
                  </button>
                </form>
                <form action="{{ route('preferences.locale', 'en') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'en' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>English</span>
                  </button>
                </form>
                <form action="{{ route('preferences.locale', 'ar') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>العربية</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
          <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 text-slate-700 hover:text-primary-600 font-black text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_signin') }}</a>
          <a href="{{ route('login') }}" class="hidden sm:inline-flex px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-xs uppercase tracking-[0.1em] shadow-xl hover:bg-primary-600 hover:-translate-y-0.5 transition-all active:scale-95">
            {{ __('public.nav_portal') }}
          </a>
          <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-label="Menu Navigasi" class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-primary-600 transition-colors">
            <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-slate-200 bg-white/95 backdrop-blur-md px-6 py-4 space-y-2 shadow-lg" style="display: none;">
      <a @click="mobileMenuOpen = false" href="{{ route('home') }}#fitur" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_features') }}</a>
      <div class="grid grid-cols-2 gap-1.5 py-1">
        <a href="{{ route('public.about') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_about') }}</a>
        <a href="{{ route('public.facilities') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_facilities') }}</a>
        <a href="{{ route('public.achievements') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_achievements') }}</a>
        <a href="{{ route('public.staff-directory') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_staff') }}</a>
        <a href="{{ route('public.virtual-tour') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_virtual_tour') }}</a>
        <a href="{{ route('public.contact') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_contact') }}</a>
      </div>
      <a @click="mobileMenuOpen = false" href="{{ route('public.posts') }}" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_news') }}</a>
      <a @click="mobileMenuOpen = false" href="{{ route('public.gallery') }}" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_gallery') }}</a>
      <a @click="mobileMenuOpen = false" href="{{ route('spmb.welcome') }}" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-primary-50 hover:text-primary-600">{{ __('public.nav_spmb') }}</a>
      @if(isset($navPages))
        @foreach($navPages as $navPage)
          <a @click="mobileMenuOpen = false" href="{{ route('public.page', $navPage->slug) }}" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-primary-50 hover:text-primary-600">{{ $navPage->title }}</a>
        @endforeach
      @endif
      <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
        <a href="{{ route('login') }}" class="flex-1 text-center py-3 rounded-xl bg-slate-900 text-white font-black text-xs uppercase tracking-widest hover:bg-primary-600 transition-colors">
          {{ __('public.nav_portal') }}
        </a>
      </div>
    </div>
  </nav>

  <!-- Subtle Top Atmospheric Wash -->
  <div class="fixed inset-x-0 top-0 h-96 pointer-events-none -z-10 bg-gradient-to-b from-emerald-50/70 via-slate-50/40 to-transparent"></div>

  <!-- Page Content -->
  <main class="min-h-screen pt-32 pb-24">
    @yield('content')
  </main>

  <!-- Footer -->
  <footer class="bg-slate-950 border-t border-slate-900 pt-24 pb-12">
    <div class="w-full px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8 mb-20">
        <!-- Branding -->
        <div class="space-y-8">
          <div class="h-14 w-auto inline-flex items-center justify-center p-2 rounded-2xl bg-white shadow-xl">
            <img src="{{ asset('images/logo-lonjong.png') }}" alt="{{ school_short_name() }}" class="h-full object-contain" onerror="this.style.display='none'">
            <span class="ms-3 font-black text-slate-900 text-sm tracking-tight uppercase">{{ school_short_name() }}</span>
          </div>
          <p class="text-slate-400 text-base leading-relaxed font-medium">
            {{ __('public.footer_tagline') }}
          </p>
        </div>

        <!-- Links 1 -->
        <div class="lg:ps-8">
          <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
            <span class="w-8 h-px bg-primary-600 me-3"></span>
            {{ __('public.footer_header_portal') }}
          </h5>
          <ul class="space-y-4">
            <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_portal') }}</a></li>
            <li><a href="{{ route('public.posts') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_news') }}</a></li>
            <li><a href="{{ route('public.gallery') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_gallery') }}</a></li>
            <li><a href="{{ route('spmb.welcome') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('spmb.spmb_title') }}</a></li>
          </ul>
        </div>

        <!-- Links 2 -->
        <div class="lg:ps-8">
          <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
            <span class="w-8 h-px bg-primary-600 me-3"></span>
            {{ __('public.footer_header_pages') }}
          </h5>
          <ul class="space-y-4">
            <li><a href="{{ route('public.about') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_about') }}</a></li>
            <li><a href="{{ route('public.facilities') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_facilities') }}</a></li>
            <li><a href="{{ route('public.achievements') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_achievements') }}</a></li>
            <li><a href="{{ route('public.staff-directory') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_staff') }}</a></li>
            <li><a href="{{ route('public.virtual-tour') }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_virtual_tour') }}</a></li>
            @if(isset($navPages))
              @foreach($navPages as $navPage)
                <li><a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ $navPage->title }}</a></li>
              @endforeach
            @endif
          </ul>
        </div>

        <!-- Contact -->
        <div class="lg:ps-8">
          <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
            <span class="w-8 h-px bg-primary-600 me-3"></span>
            {{ __('public.footer_header_contact') }}
          </h5>
          <ul class="space-y-6">
            <li class="flex items-start">
              <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 me-4">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
              </div>
              <span class="text-slate-400 text-sm leading-relaxed font-medium pt-1">{{ __('public.footer_address') }}</span>
            </li>
            <li class="flex items-center">
              <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 me-4">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"></path></svg>
              </div>
              <span class="text-slate-400 text-sm font-medium">{{ school_email() }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="border-t border-slate-900 pt-12 flex flex-col md:flex-row justify-between items-center gap-6">
        <p class="text-slate-500 text-sm font-medium">
          &copy; {{ date('Y') }} {{ __('public.footer_copyright', school_placeholders()) }}
        </p>
        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">
          Sulaone — SISTA {{ school_short_name() }}
        </p>
      </div>
    </div>
  </footer>
  @include('partials.pwa-feedback')

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
</body>
</html>
