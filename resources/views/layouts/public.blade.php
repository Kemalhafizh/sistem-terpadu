<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @yield('seo_meta')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f8fafc; /* Tailwind slate-50 */
        }
        
        /* Glassmorphism Utilities */
        .glass {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }
        .glass-dark {
            background: rgba(15, 23, 42, 0.8); /* Slate 900 */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Custom Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; }

        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden selection:bg-primary-500 selection:text-white" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- Navigation -->
    <nav :class="{'py-3 bg-white/95 backdrop-blur-md shadow-lg border-b border-slate-200': scrolled, 'py-5 bg-transparent': !scrolled}" 
         class="fixed top-0 w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <a href="{{ route('home') }}" class="flex items-center space-x-3">
                    <div class="h-12 w-auto flex items-center justify-center p-1.5 rounded-xl bg-white shadow-sm border border-slate-100">
                        <img src="{{ asset('images/logo-lonjong.png') }}" alt="Sultan Agung 1" class="h-full object-contain" 
                             onerror="this.parentElement.innerHTML = '<span class=\'text-primary-700 font-bold text-sm px-2\'>SA-1</span>'">
                    </div>
                    <div>
                        <h1 class="text-sm font-black text-slate-900 leading-tight">SMA ISLAM<br><span class="text-[10px] font-bold text-primary-600 tracking-[0.2em]">SULTAN AGUNG 1</span></h1>
                    </div>
                </a>
            </div>

            <div class="flex items-center space-x-4 md:space-x-8 rtl:space-x-reverse">
                <div class="hidden lg:flex items-center space-x-6 rtl:space-x-reverse">
                    <a href="{{ route('home') }}#fitur" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_features') }}</a>
                    <a href="{{ route('public.posts') }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_news') }}</a>
                    <a href="{{ route('public.gallery') }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_gallery') }}</a>
                    <a href="{{ route('home') }}#spmb" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_spmb') }}</a>
                    @if(isset($navPages))
                        @foreach($navPages as $navPage)
                            <a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-600 hover:text-primary-600 font-bold text-xs uppercase tracking-widest transition-colors">{{ $navPage->title }}</a>
                        @endforeach
                    @endif
                </div>
                <div class="flex items-center space-x-3 rtl:space-x-reverse">
                    <!-- Language Selector Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-1.5 px-3 py-2 rounded-xl bg-white/40 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 hover:bg-white/80 transition-all text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            <span>{{ app()->getLocale() === 'id' ? '🇮🇩 ID' : (app()->getLocale() === 'en' ? '🇺🇸 EN' : '🇸🇦 AR') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 rtl:left-0 rtl:right-auto mt-2 w-36 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-800 z-50 overflow-hidden" 
                             style="display:none">
                            <div class="p-1.5 space-y-1">
                                <form action="{{ route('preferences.locale', 'id') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'id' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span>🇮🇩 Indonesia</span>
                                    </button>
                                </form>
                                <form action="{{ route('preferences.locale', 'en') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'en' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span>🇺🇸 English</span>
                                    </button>
                                </form>
                                <form action="{{ route('preferences.locale', 'ar') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left rtl:text-right px-3 py-2 rounded-xl text-xs font-bold flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'bg-primary-50 text-primary-600' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <span>🇸🇦 العربية</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('login') }}" class="px-4 py-2 text-slate-700 hover:text-primary-600 font-black text-xs uppercase tracking-widest transition-colors">{{ __('public.nav_signin') }}</a>
                    <a href="{{ route('login') }}" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-xs uppercase tracking-[0.1em] shadow-xl hover:bg-primary-600 hover:-translate-y-0.5 transition-all active:scale-95">
                        {{ __('public.nav_portal') }}
                    </a>
                </div>
        </div>
    </nav>

    <!-- Background Animated Blobs -->
    <div class="fixed inset-0 w-full h-full pointer-events-none -z-10 overflow-hidden">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-primary-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-emerald-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-teal-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Page Content -->
    <main class="min-h-screen pt-32 pb-24">
        @yield('content')
    </main>

    <!-- Premium Footer -->
    <footer class="bg-slate-950 border-t border-slate-900 pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8 mb-20">
                <!-- Branding -->
                <div class="space-y-8">
                    <div class="h-14 w-auto inline-flex items-center justify-center p-2 rounded-2xl bg-white shadow-xl">
                        <img src="{{ asset('images/logo-lonjong.png') }}" alt="SA 1" class="h-full object-contain" onerror="this.style.display='none'">
                        <span class="ml-3 font-black text-slate-900 text-sm tracking-tight uppercase">Sultan Agung 1</span>
                    </div>
                    <p class="text-slate-400 text-base leading-relaxed font-medium">
                        {{ __('public.footer_tagline') }}
                    </p>
                </div>

                <!-- Links 1 -->
                <div class="lg:pl-8 rtl:pr-8 rtl:pl-0">
                    <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3 rtl:ml-3 rtl:mr-0"></span>
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
                <div class="lg:pl-8 rtl:pr-8 rtl:pl-0">
                    <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3 rtl:ml-3 rtl:mr-0"></span>
                        {{ __('public.footer_header_pages') }}
                    </h5>
                    <ul class="space-y-4">
                        @if(isset($navPages))
                            @foreach($navPages as $navPage)
                                <li><a href="{{ route('public.page', $navPage->slug) }}" class="text-slate-400 hover:text-primary-400 transition-colors font-bold text-sm tracking-wide">{{ $navPage->title }}</a></li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                <!-- Contact -->
                <div class="lg:pl-8 rtl:pr-8 rtl:pl-0">
                    <h5 class="text-white font-black mb-8 uppercase text-xs tracking-[0.2em] flex items-center">
                        <span class="w-8 h-px bg-primary-600 mr-3 rtl:ml-3 rtl:mr-0"></span>
                        {{ __('public.footer_header_contact') }}
                    </h5>
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 mr-4">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <span class="text-slate-400 text-sm leading-relaxed font-medium pt-1">{{ __('public.footer_address') }}</span>
                        </li>
                        <li class="flex items-center">
                            <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center shrink-0 border border-slate-800 mr-4">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"></path></svg>
                            </div>
                            <span class="text-slate-400 text-sm font-medium">info@sultanagung1.sch.id</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-900 pt-12 flex flex-col md:flex-row justify-between items-center gap-8">
                <p class="text-slate-500 text-sm font-medium">
                    &copy; {{ date('Y') }} {{ __('public.footer_copyright') }}
                </p>
                <div class="flex space-x-8">
                    <a href="#" class="text-slate-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors">Privacy Policy</a>
                    <a href="#" class="text-slate-500 hover:text-white text-xs font-bold uppercase tracking-widest transition-colors">Terms</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
