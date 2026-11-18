<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sulaone — Portal Sekolah {{ school_name() }}</title>
  
  <!-- SEO Meta Tags -->
  <meta name="description" content="Sulaone — Sistem Informasi Sekolah Terpadu (SISTA) {{ school_name() }}. Portal layanan informasi akademik, keuangan, keagamaan, dan pendaftaran siswa baru secara online.">
  <meta name="keywords" content="sulaone, sista, {{ mb_strtolower(school_name()) }}, sekolah islam, portal sekolah, spmb online">
  <meta name="author" content="{{ school_name() }}">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:site_name" content="Sulaone — SISTA {{ school_short_name() }}">
  <meta property="og:title" content="Sulaone — Portal Sekolah {{ school_name() }}">
  <meta property="og:description" content="Portal layanan informasi akademik, keuangan, keagamaan, dan pendaftaran siswa baru secara online di {{ school_name() }}.">
  <meta property="og:image" content="{{ asset('images/logo-kotak.png') }}">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title" content="Sulaone — Portal Sekolah {{ school_name() }}">
  <meta property="twitter:description" content="Portal layanan informasi akademik, keuangan, keagamaan, dan pendaftaran siswa baru secara online di {{ school_name() }}.">
  <meta property="twitter:image" content="{{ asset('images/logo-kotak.png') }}">

  <!-- PWA & Favicon Set -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/logo-kotak.png') }}">
  <link rel="manifest" href="/manifest.json">
  <meta name="theme-color" content="#0d9488">

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
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    /* Hide scrollbar for testimonial slider */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden selection:bg-teal-500 selection:text-white" 
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
  <nav :class="{'py-3 bg-white/95 backdrop-blur-md shadow-lg border-b border-slate-200/70': scrolled || mobileMenuOpen, 'py-5 bg-white/75 backdrop-blur-sm border-b border-slate-100/60': !scrolled && !mobileMenuOpen}" 
     class="fixed top-0 w-full z-50 transition-all duration-300">
     <div class="w-full px-6 flex justify-between items-center">
      <a href="{{ route('home') }}" class="flex items-center gap-3">
        <div class="h-12 w-auto flex items-center justify-center p-1.5 rounded-xl bg-white shadow-sm border border-slate-100">
          <img src="{{ asset('images/logo-lonjong.png') }}" alt="{{ school_short_name() }}" class="h-full object-contain" 
             onerror="this.parentElement.innerHTML = '<span class=\'text-teal-700 font-bold text-sm px-2\'>Sulaone</span>'">
        </div>
        <div class="hidden sm:block">
          <h1 class="text-sm font-black text-slate-900 leading-tight">Sulaone<br><span class="text-[10px] font-bold text-teal-600 tracking-wider uppercase">SISTA • {{ school_short_name() }}</span></h1>
        </div>
      </a>

      <div class="flex items-center gap-4 md:gap-8">
        <div class="hidden lg:flex items-center gap-6">
          <a href="#fitur" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_features') }}</a>
          <!-- Profil Dropdown -->
          <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors flex items-center gap-1">
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
               class="absolute start-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden py-1"
               style="display: none;">
              <a href="{{ route('public.about') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_about') }}</a>
              <a href="{{ route('public.facilities') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_facilities') }}</a>
              <a href="{{ route('public.achievements') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_achievements') }}</a>
              <a href="{{ route('public.staff-directory') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_staff') }}</a>
              <a href="{{ route('public.virtual-tour') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_virtual_tour') }}</a>
              <a href="{{ route('public.contact') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-teal-50 hover:text-teal-600 transition-colors">{{ __('public.nav_contact') }}</a>
            </div>
          </div>
          <a href="#berita" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_news') }}</a>
          <a href="#galeri" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_gallery') }}</a>
          <a href="#spmb" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_spmb') }}</a>
          @if(isset($navPages))
            @foreach($navPages as $navPage)
              <a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-600 hover:text-teal-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ $navPage->title }}</a>
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
               class="absolute end-0 mt-2 w-36 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden" 
               style="display:none">
              <div class="p-1.5 space-y-1">
                <form action="{{ route('preferences.locale', 'id') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'id' ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>Indonesia</span>
                  </button>
                </form>
                <form action="{{ route('preferences.locale', 'en') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'en' ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>English</span>
                  </button>
                </form>
                <form action="{{ route('preferences.locale', 'ar') }}" method="POST" class="block">
                  @csrf
                  <button type="submit" class="w-full text-start px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'bg-teal-50 text-teal-600' : 'text-slate-600 hover:bg-slate-50' }}">
                    <span>العربية</span>
                  </button>
                </form>
              </div>
            </div>
          </div>
          <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 text-slate-700 hover:text-teal-600 font-black text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_signin') }}</a>
          <a href="{{ route('login') }}" class="hidden sm:inline-flex px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-xs uppercase tracking-[0.1em] shadow-xl hover:bg-teal-600 hover:-translate-y-0.5 transition-all active:scale-95">
            {{ __('public.nav_portal') }}
          </a>
          <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-label="Menu Navigasi" class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-teal-600 transition-colors">
            <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            <svg x-show="mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
      </div>
     </div>

     <!-- Mobile Navigation Drawer -->
     <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-slate-200 bg-white/95 backdrop-blur-md px-6 py-4 space-y-2 shadow-lg" style="display: none;">
       <a @click="mobileMenuOpen = false" href="#fitur" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_features') }}</a>
       <div class="grid grid-cols-2 gap-1.5 py-1">
         <a href="{{ route('public.about') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_about') }}</a>
         <a href="{{ route('public.facilities') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_facilities') }}</a>
         <a href="{{ route('public.achievements') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_achievements') }}</a>
         <a href="{{ route('public.staff-directory') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_staff') }}</a>
         <a href="{{ route('public.virtual-tour') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_virtual_tour') }}</a>
         <a href="{{ route('public.contact') }}" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_contact') }}</a>
       </div>
       <a @click="mobileMenuOpen = false" href="#berita" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_news') }}</a>
       <a @click="mobileMenuOpen = false" href="#galeri" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_gallery') }}</a>
       <a @click="mobileMenuOpen = false" href="#spmb" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-teal-50 hover:text-teal-600">{{ __('public.nav_spmb') }}</a>
       @if(isset($navPages))
         @foreach($navPages as $navPage)
           <a @click="mobileMenuOpen = false" href="{{ route('public.page', $navPage->slug) }}" class="block px-3 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-700 hover:bg-teal-50 hover:text-teal-600">{{ $navPage->title }}</a>
         @endforeach
       @endif
       <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
         <a href="{{ route('login') }}" class="flex-1 text-center py-3 rounded-xl bg-slate-900 text-white font-black text-xs uppercase tracking-widest hover:bg-teal-600 transition-colors">
           {{ __('public.nav_portal') }}
         </a>
       </div>
     </div>
  </nav>

  <!-- Subtle Top Atmospheric Wash -->
  <div class="fixed inset-x-0 top-0 h-96 pointer-events-none -z-10 bg-gradient-to-b from-teal-50/70 via-slate-50/40 to-transparent"></div>

  <!-- 1. Hero Section -->
  <section class="relative pt-32 pb-20 lg:pt-40 lg:pb-28 px-6 overflow-hidden">
    <div class="w-full max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-10 items-center">
      
      <!-- Left Content -->
      <div class="space-y-8 z-10">
        <div class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur-md rounded-full border border-slate-200 shadow-sm">
          <span class="flex h-2 w-2 rounded-full bg-teal-600 me-2"></span>
          <span class="text-xs font-bold text-slate-700 uppercase tracking-widest">{{ __('public.hero_badge') }}</span>
        </div>
        
        <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] tracking-tight">
          {{ __('public.hero_title_1') }} <br>
          <span class="text-teal-600">{{ __('public.hero_title_2') }}</span>
        </h1>
        
        <p class="text-lg text-slate-600 max-w-lg leading-relaxed font-medium">
          {{ __('public.hero_desc', school_placeholders()) }}
        </p>

        <div class="flex flex-col sm:flex-row gap-4">
          <a href="{{ route('login') }}" class="px-8 py-4 bg-teal-600 text-white rounded-2xl font-bold text-base shadow-xl shadow-teal-600/20 hover:bg-teal-700 hover:-translate-y-0.5 transition-all flex items-center justify-center group">
            {{ __('public.cta_login') }}
            <svg class="w-5 h-5 ms-2 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
          </a>
          <a href="#spmb" class="px-8 py-4 bg-white text-slate-800 border border-slate-200 rounded-2xl font-bold text-base shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all text-center">
            {{ __('public.cta_spmb') }}
          </a>
        </div>
      </div>

      <!-- Right Content (Institutional Visual Showcase) -->
      <div class="relative z-10 w-full">
        <div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-slate-200/80">
          <div class="relative aspect-[4/3] bg-slate-900">
            <img src="{{ asset('images/login-hero.jpg') }}" alt="{{ school_short_name() }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-6 sm:p-8">
              <span class="text-[11px] font-black uppercase tracking-[0.2em] text-teal-300 mb-1">Sulaone — SISTA</span>
              <p class="text-white text-xl sm:text-2xl font-black tracking-tight">{{ school_name() }}</p>
            </div>
          </div>
          <div class="grid grid-cols-3 divide-x rtl:divide-x-reverse divide-slate-100 bg-slate-50/80 text-center py-4 px-2">
            <div class="px-2">
              <p class="text-xs font-black text-slate-800">Akademik & E-Rapor</p>
              <p class="text-[11px] text-slate-500 mt-0.5">Nilai & Kurikulum</p>
            </div>
            <div class="px-2">
              <p class="text-xs font-black text-slate-800">Keuangan Terpadu</p>
              <p class="text-[11px] text-slate-500 mt-0.5">Tagihan & Kwitansi</p>
            </div>
            <div class="px-2">
              <p class="text-xs font-black text-slate-800">Mutaba'ah & Tahfidz</p>
              <p class="text-[11px] text-slate-500 mt-0.5">Karakter & Ibadah</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 2. Interactive Counter Section -->
  <section class="w-full max-w-7xl mx-auto px-6 pb-20">
    <div class="glass rounded-[2.5rem] p-8 md:p-12 shadow-sm border border-slate-200/60">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x rtl:divide-x-reverse divide-slate-100">
        
        <!-- Alpine Component for Counter -->
        <div x-data="counterComponent({{ $studentCount ?? 0 }}, '+')" x-intersect.once="start()">
          <p class="text-4xl md:text-5xl font-black text-slate-900 mb-2">
            <span x-text="current">0</span><span class="text-teal-600" x-text="suffix"></span>
          </p>
          <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('public.stats_students') }}</p>
        </div>

        <div x-data="counterComponent({{ $teacherStaffCount ?? 0 }}, '+')" x-intersect.once="start()">
          <p class="text-4xl md:text-5xl font-black text-slate-900 mb-2">
            <span x-text="current">0</span><span class="text-teal-600" x-text="suffix"></span>
          </p>
          <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('public.stats_teachers') }}</p>
        </div>

        <div x-data="counterComponent({{ $classroomCount ?? 0 }}, '+')" x-intersect.once="start()">
          <p class="text-4xl md:text-5xl font-black text-slate-900 mb-2">
            <span x-text="current">0</span><span class="text-teal-600" x-text="suffix"></span>
          </p>
          <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('public.stats_classes') }}</p>
        </div>

        <div>
          <p class="text-4xl md:text-5xl font-black text-teal-600 mb-2">
            {{ school_accreditation() ?: 'A' }}
          </p>
          <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ __('public.stats_accreditation') }}</p>
        </div>

      </div>
    </div>
  </section>

  <!-- 3. Platform Features (Bento Grid) -->
  <section id="fitur" class="py-24 bg-white relative">
    <div class="w-full max-w-7xl mx-auto px-6">
      <div class="text-center max-w-3xl mx-auto mb-20">
        <h2 class="text-teal-600 font-black tracking-[0.2em] uppercase text-xs mb-4">Platform Terpadu</h2>
        <h3 class="text-4xl md:text-5xl font-black text-slate-900 mb-6 tracking-tight">Solusi Digital Terintegrasi</h3>
        <p class="text-slate-500 text-lg font-medium leading-relaxed">Sistem manajemen pendidikan modern yang menghubungkan seluruh elemen sekolah dalam satu portal yang aman dan efisien.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Feature 1: Akademik -->
        <div class="lg:col-span-2 bg-gradient-to-br from-slate-50 to-white rounded-[2.5rem] p-10 border border-slate-100 relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
          <div class="absolute -top-12 -end-12 opacity-[0.03] group-hover:opacity-[0.08] transition-opacity pointer-events-none">
            <svg class="w-64 h-64 text-teal-900" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path></svg>
          </div>
          <div class="relative z-10">
            <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-teal-600 mb-8 border border-slate-50">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <h4 class="text-3xl font-black text-slate-900 mb-4 tracking-tight">Akademik & E-Rapor</h4>
            <p class="text-slate-500 leading-relaxed text-lg max-w-md font-medium">Manajemen kurikulum otomatis dengan pengolahan nilai yang presisi. Memberikan kemudahan bagi guru dan transparansi bagi wali murid.</p>
          </div>
        </div>

        <!-- Feature 2: Keuangan -->
        <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
          <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center mb-8 border border-white/10">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          </div>
          <h4 class="text-2xl font-black mb-4 tracking-tight">Administrasi Keuangan</h4>
          <p class="text-slate-400 font-medium leading-relaxed">Pemantauan tagihan SPP, riwayat transaksi, dan pencetakan kwitansi resmi sekolah secara transparan dan akuntabel.</p>
        </div>

        <!-- Feature 3: BPI -->
        <div class="bg-teal-600 rounded-[2.5rem] p-10 text-white relative overflow-hidden group hover:shadow-2xl transition-all duration-500">
          <div class="w-16 h-16 bg-white/20 rounded-2xl backdrop-blur-md flex items-center justify-center mb-8 border border-white/20">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
          </div>
          <h4 class="text-2xl font-black mb-4 tracking-tight">Bina Pribadi Islami</h4>
          <p class="text-teal-50 font-medium leading-relaxed">Pantau perkembangan karakter, setoran hafalan Tahfidz, dan ibadah harian siswa secara komprehensif.</p>
        </div>

        <!-- Feature 4: BK -->
        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 group hover:shadow-2xl transition-all duration-500">
          <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-8 border border-amber-100">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <h4 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">Poin Kedisiplinan</h4>
          <p class="text-slate-500 font-medium leading-relaxed">Catatan prestasi dan pelanggaran siswa yang dapat diakses langsung oleh orang tua untuk kolaborasi pembinaan.</p>
        </div>

        <!-- Feature 5: Library -->
        <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 group hover:shadow-2xl transition-all duration-500">
          <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-8 border border-blue-100">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
          </div>
          <h4 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">E-Library Portal</h4>
          <p class="text-slate-500 font-medium leading-relaxed">Akses ke ribuan literatur digital dan buku teks sekolah untuk menunjang kegiatan belajar mengajar.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- 4. Dynamic News & Announcements Section -->
  <section id="berita" class="py-24 bg-slate-50 border-t border-b border-slate-200/60 relative">
    <div class="w-full max-w-7xl mx-auto px-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 gap-4">
        <div>
          <h2 class="text-teal-600 font-black tracking-[0.2em] uppercase text-xs mb-4">Warta Sekolah</h2>
          <h3 class="text-4xl font-black text-slate-900 tracking-tight">Berita & Kegiatan Terkini</h3>
        </div>
        <a href="{{ route('public.posts') }}" class="px-6 py-3 bg-white hover:bg-slate-100 text-slate-800 font-bold rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center group">
          Semua Berita
          <svg class="w-4 h-4 ms-2 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @if(isset($posts) && $posts->count() > 0)
          @foreach($posts as $post)
            <article class="glass rounded-[2rem] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group border border-slate-200/60">
              <div class="relative h-56 w-full overflow-hidden">
                @if($post->cover_image)
                  <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                  <div class="w-full h-full bg-gradient-to-br from-teal-500 to-emerald-700 flex items-center justify-center text-white">
                    <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                  </div>
                @endif
                <div class="absolute top-4 start-4">
                  <span class="px-3 py-1 bg-white/90 backdrop-blur-sm rounded-full text-[10px] font-black text-teal-700 uppercase tracking-wider shadow-sm">{{ $post->category }}</span>
                </div>
              </div>
              <div class="p-8 flex-1 flex flex-col justify-between">
                <div>
                  <div class="flex items-center gap-3 text-xs text-slate-400 mb-3 font-semibold">
                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                    <span>•</span>
                    <span>{{ $post->view_count }} Views</span>
                  </div>
                  <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-teal-600 transition-colors leading-snug">
                    <a href="{{ route('public.post', $post->slug) }}">{{ $post->title }}</a>
                  </h4>
                  <p class="text-slate-500 text-sm leading-relaxed mb-6 font-medium">
                    {{ $post->excerpt ?? Str::limit(strip_tags($post->body), 100) }}
                  </p>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-xs text-slate-700">
                      {{ strtoupper(substr($post->author->name ?? 'A', 0, 2)) }}
                    </div>
                    <span class="text-xs font-bold text-slate-700">{{ $post->author->name ?? 'Humas' }}</span>
                  </div>
                  <a href="{{ route('public.post', $post->slug) }}" class="text-teal-600 hover:text-teal-700 font-bold text-xs uppercase tracking-wider inline-flex items-center gap-1">
                    <span>Selengkapnya</span>
                    <span class="rtl:rotate-180 inline-block">&rarr;</span>
                  </a>
                </div>
              </div>
            </article>
          @endforeach
        @else
          <div class="col-span-full glass rounded-2xl p-12 text-center border border-slate-200">
            <p class="text-slate-500 font-medium">Belum ada berita atau kegiatan yang dipublikasikan.</p>
          </div>
        @endif
      </div>
    </div>
  </section>

  <!-- 5. Dynamic School Gallery Section -->
  <section id="galeri" class="py-24 bg-white relative">
    <div class="w-full max-w-7xl mx-auto px-6">
      <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 gap-4">
        <div>
          <h2 class="text-teal-600 font-black tracking-[0.2em] uppercase text-xs mb-4">Galeri Dokumentasi</h2>
          <h3 class="text-4xl font-black text-slate-900 tracking-tight">Potret Sarana & Kegiatan</h3>
        </div>
        <a href="{{ route('public.gallery') }}" class="px-6 py-3 bg-white hover:bg-slate-100 text-slate-800 font-bold rounded-xl border border-slate-200 shadow-sm transition-all inline-flex items-center group">
          Lihat Seluruh Galeri
          <svg class="w-4 h-4 ms-2 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @if(isset($galleries) && $galleries->count() > 0)
          @foreach($galleries->take(8) as $gallery)
            <div class="group relative h-64 rounded-3xl overflow-hidden shadow-sm border border-slate-100 cursor-pointer">
              <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 p-6 flex flex-col justify-end">
                <span class="text-[9px] font-black text-teal-400 uppercase tracking-widest mb-1">{{ $gallery->category }}</span>
                <h4 class="text-white font-bold text-base mb-1 leading-snug">{{ $gallery->title }}</h4>
                @if($gallery->caption)
                  <p class="text-slate-300 text-xs line-clamp-2">{{ $gallery->caption }}</p>
                @endif
              </div>
            </div>
          @endforeach
        @else
          <div class="col-span-full glass rounded-2xl p-12 text-center border border-slate-200">
            <p class="text-slate-500 font-medium">Foto dokumentasi galeri belum tersedia.</p>
          </div>
        @endif
      </div>
    </div>
  </section>

  <!-- 6. SPMB Flow Section -->
  <section id="spmb" class="py-24 bg-slate-50 border-t border-slate-200">
    <div class="w-full max-w-7xl mx-auto px-6">
      <div class="flex flex-col lg:flex-row items-center gap-16">
        <!-- Left: Info -->
        <div class="lg:w-1/3">
          <div class="inline-flex items-center px-3 py-1 bg-teal-100 text-teal-700 rounded-full text-xs font-bold tracking-widest uppercase mb-4">
            Penerimaan Siswa Baru
          </div>
          <h2 class="text-4xl font-black text-slate-900 mb-6">Bergabunglah Bersama Kami</h2>
          <p class="text-slate-600 text-lg mb-8 leading-relaxed">
            Proses pendaftaran Seleksi Penerimaan Murid Baru (SPMB) kini 100% online. Mudah, transparan, dan dapat dipantau dari rumah.
          </p>
          <a href="{{ route('spmb.create') }}" class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-bold text-base shadow-xl hover:bg-teal-600 hover:shadow-teal-600/30 transition-all inline-flex items-center gap-2">
            <span>Daftar SPMB</span>
            <span class="rtl:rotate-180 inline-block">&rarr;</span>
          </a>
        </div>

        <!-- Right: Flow Steps -->
        <div class="lg:w-2/3 w-full relative">
          <div class="absolute start-[39px] top-4 bottom-4 w-1 bg-slate-200 rounded-full z-0 hidden sm:block"></div>
          
          <div class="space-y-8 relative z-10">
            <div class="flex items-start bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
              <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center shrink-0 border-2 border-white shadow-md text-2xl font-black">1</div>
              <div class="ms-6">
                <h4 class="text-xl font-bold text-slate-900 mb-2">Buat Akun Portal</h4>
                <p class="text-slate-500">Mendaftar menggunakan Email dan Nomor WhatsApp aktif untuk memantau status seleksi.</p>
              </div>
            </div>
            <div class="flex items-start bg-white p-6 rounded-3xl shadow-sm border border-slate-100 ms-0 sm:ms-8">
              <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center shrink-0 border-2 border-white shadow-md text-2xl font-black">2</div>
              <div class="ms-6">
                <h4 class="text-xl font-bold text-slate-900 mb-2">Lengkapi Berkas Digital</h4>
                <p class="text-slate-500">Unggah pindaian KK, Akta Kelahiran, dan Surat Keterangan Lulus (SKL) / Rapor SMP.</p>
              </div>
            </div>
            <div class="flex items-start bg-white p-6 rounded-3xl shadow-sm border border-slate-100 ms-0 sm:ms-16">
              <div class="w-20 h-20 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center shrink-0 border-2 border-white shadow-md text-2xl font-black">3</div>
              <div class="ms-6">
                <h4 class="text-xl font-bold text-slate-900 mb-2">CBT & Wawancara</h4>
                <p class="text-slate-500">Mengikuti Computer Based Test (CBT) dan wawancara komitmen keislaman sesuai jadwal seleksi.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @if(isset($testimonials) && $testimonials->count() > 0)
  <!-- 7. Dynamic Testimonial Slider -->
  <section class="py-24 bg-teal-900 text-white overflow-hidden relative">
    <div class="w-full max-w-7xl mx-auto px-6 relative z-10 text-center mb-12">
      <h2 class="text-3xl md:text-4xl font-black mb-4">Apa Kata Mereka?</h2>
      <p class="text-teal-200">Suara dari alumni, guru, dan wali murid tentang {{ school_short_name() }}.</p>
    </div>

    <div x-data="testimonialCarousel()" class="relative w-full max-w-7xl mx-auto px-6">
      <!-- Testimonial Wrapper -->
      <div x-ref="container" class="flex overflow-x-auto gap-6 pb-12 snap-x snap-mandatory hide-scrollbar max-w-5xl mx-auto" style="scroll-padding-inline-start: 1.5rem;">
        @foreach($testimonials as $testimonial)
          <div class="min-w-[280px] md:min-w-[380px] glass-dark p-8 rounded-3xl snap-start flex flex-col justify-between border border-white/10">
            <div>
              <div class="flex text-amber-400 mb-4">
                @for($i = 0; $i < 5; $i++)
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                @endfor
              </div>
              <p class="text-teal-100 mb-6 italic leading-relaxed">"{{ $testimonial->content }}"</p>
            </div>
            <div class="flex items-center mt-6">
              @if($testimonial->avatar_path)
                <img src="{{ asset('storage/' . $testimonial->avatar_path) }}" alt="{{ $testimonial->name }}" class="w-10 h-10 rounded-full border-2 border-teal-400 object-cover">
              @else
                <div class="w-10 h-10 bg-teal-700 rounded-full border-2 border-teal-400 flex items-center justify-center font-bold text-sm">
                  {{ strtoupper(substr($testimonial->name ?? 'A', 0, 2)) }}
                </div>
              @endif
              <div class="ms-3">
                <p class="font-bold text-white text-sm">{{ $testimonial->name }}</p>
                <p class="text-xs text-teal-300">{{ $testimonial->role }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Controller Buttons -->
      <div class="flex justify-center gap-4 mt-6">
        <button aria-label="Sebelumnya" @click="prev()" class="w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center border border-white/10 text-white transition-all active:scale-95">
          <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        </button>
        <button aria-label="Selanjutnya" @click="next()" class="w-12 h-12 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center border border-white/10 text-white transition-all active:scale-95">
          <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </button>
      </div>
    </div>
  </section>
  @endif

  <!-- 8. Footer -->
  <footer class="bg-slate-950 border-t border-slate-900 pt-24 pb-12">
    <div class="w-full max-w-7xl mx-auto px-6">
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
            <span class="w-8 h-px bg-teal-600 me-3"></span>
            {{ __('public.footer_header_portal') }}
          </h5>
          <ul class="space-y-4">
            <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_portal') }}</a></li>
            <li><a href="{{ route('public.posts') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_news') }}</a></li>
            <li><a href="{{ route('public.gallery') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_gallery') }}</a></li>
            <li><a href="{{ route('spmb.welcome') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('spmb.spmb_title') }}</a></li>
          </ul>
        </div>

        <!-- Links 2 -->
        <div class="lg:ps-8">
          <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
            <span class="w-8 h-px bg-teal-600 me-3"></span>
            {{ __('public.footer_header_pages') }}
          </h5>
          <ul class="space-y-4">
            <li><a href="{{ route('public.about') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_about') }}</a></li>
            <li><a href="{{ route('public.facilities') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_facilities') }}</a></li>
            <li><a href="{{ route('public.achievements') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_achievements') }}</a></li>
            <li><a href="{{ route('public.staff-directory') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_staff') }}</a></li>
            <li><a href="{{ route('public.virtual-tour') }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ __('public.nav_virtual_tour') }}</a></li>
            @if(isset($navPages))
              @foreach($navPages as $navPage)
                <li><a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-400 hover:text-teal-400 transition-colors font-bold text-sm tracking-wide">{{ $navPage->title }}</a></li>
              @endforeach
            @endif
          </ul>
        </div>

        <!-- Contact -->
        <div class="lg:ps-8">
          <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
            <span class="w-8 h-px bg-teal-600 me-3"></span>
            {{ __('public.footer_header_contact') }}
          </h5>
          <ul class="space-y-6">
            <li class="flex items-start">
              <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 me-4">
                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
              </div>
              <span class="text-slate-400 text-sm leading-relaxed font-medium pt-1">{{ __('public.footer_address') }}</span>
            </li>
            <li class="flex items-center">
              <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 me-4">
                <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"></path></svg>
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

  <!-- Alpine.js Logic for Counter & Carousel -->
  <script>
    document.addEventListener('alpine:init', () => {
      // Stats counter count-up
      Alpine.data('counterComponent', (target, suffix) => ({
        current: 0,
        target: target,
        suffix: suffix,
        started: false,
        start() {
          if (this.started) return;
          this.started = true;
          const duration = 1200;
          let startTime = null;
          
          const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

          const step = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const progress = Math.min((timestamp - startTime) / duration, 1);
            this.current = Math.floor(easeOutQuart(progress) * this.target);
            if (progress < 1) {
              window.requestAnimationFrame(step);
            } else {
              this.current = this.target;
            }
          };
          window.requestAnimationFrame(step);
        }
      }));

      @if(isset($testimonials) && $testimonials->count() > 0)
      // Testimonials Carousel
      Alpine.data('testimonialCarousel', () => ({
        active: 0,
        total: {{ $testimonials->count() }},
        timer: null,
        next() {
          this.active = (this.active + 1) % this.total;
          this.scrollToActive();
        },
        prev() {
          this.active = (this.active - 1 + this.total) % this.total;
          this.scrollToActive();
        },
        scrollToActive() {
          const container = this.$refs.container;
          if (container && container.children[this.active]) {
            const child = container.children[this.active];
            const isRtl = document.documentElement.dir === 'rtl';
            const delta = isRtl
              ? child.getBoundingClientRect().right - container.getBoundingClientRect().right
              : child.getBoundingClientRect().left - container.getBoundingClientRect().left;
            container.scrollBy({
              left: delta,
              behavior: 'smooth'
            });
          }
        },
        init() {
          if (this.total > 1) {
            this.timer = setInterval(() => this.next(), 6000);
          }
        },
        destroy() {
          if (this.timer) clearInterval(this.timer);
        }
      }));
      @endif
    });
  </script>
  @include('partials.pwa-feedback')
</body>
</html>
