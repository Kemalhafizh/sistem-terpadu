<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Penerimaan Murid Baru (SPMB) | SMA Islam Sultan Agung 1 Semarang</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">

    <!-- Force Permanent Light Mode -->
    <script>
        document.documentElement.classList.remove('dark');
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; }
        .font-inter { font-family: 'Inter', sans-serif; }
        
        .text-glow {
            text-shadow: 0 0 35px rgba(16, 185, 129, 0.25);
        }
        
        /* Smooth Custom Transitions */
        .faq-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body 
    x-data="{ scrolled: false }" 
    @scroll.window="scrolled = (window.pageYOffset > 30)" 
    class="bg-slate-55 text-slate-800 antialiased selection:bg-emerald-500 selection:text-white transition-colors duration-300"
>

    <!-- Premium Dynamic Frosted White Navbar -->
    <nav 
        :class="scrolled 
            ? 'bg-white/90 border-b border-slate-200/60 shadow-lg shadow-slate-200/5 py-3' 
            : 'bg-transparent border-b border-transparent py-5'"
        class="fixed w-full z-50 transition-all duration-300 left-0 right-0"
    >
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Branding -->
                <div class="flex items-center group flex-nowrap">
                    <div 
                        :class="scrolled 
                            ? 'bg-emerald-50 border-emerald-100' 
                            : 'bg-white border-slate-100 shadow-sm'" 
                        class="h-11 w-11 flex items-center justify-center p-1.5 rounded-xl border backdrop-blur-sm mr-3 shrink-0 transition-all duration-300 shadow-inner group-hover:scale-105"
                    >
                        <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain filter drop-shadow-[0_2px_4px_rgba(16,185,129,0.15)]">
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight bg-gradient-to-r from-emerald-600 to-teal-700 bg-clip-text text-transparent">
                            Sultan Agung 1
                        </span>
                        <p 
                            :class="scrolled ? 'text-slate-500' : 'text-slate-650'"
                            class="text-[9px] font-bold uppercase tracking-widest leading-none mt-0.5 transition-all duration-300"
                        >
                            {{ __('spmb.spmb_portal_subtitle') }}
                        </p>
                    </div>
                </div>

                <!-- Navigation Controls -->
                <div class="flex items-center space-x-3 sm:space-x-4 rtl:space-x-reverse">
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
                    
                    <a 
                        href="{{ route('spmb.status') }}" 
                        :class="scrolled 
                            ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200' 
                            : 'bg-white/80 hover:bg-white text-slate-750 border-slate-200/70 shadow-sm'"
                        class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 border hover:scale-[1.02]"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z"></path>
                        </svg>
                        {{ __('spmb.check_status') }}
                    </a>
                    <a 
                        href="{{ route('spmb.create') }}" 
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl font-bold text-xs shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/20 hover:scale-[1.03] active:scale-[0.98] transition-all transform duration-300 flex items-center gap-2"
                    >
                        <svg class="w-3.5 h-3.5 text-emerald-100" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>
                        </svg>
                        {{ __('spmb.register_online') }}
                    </a>
                    <a 
                        href="{{ route('login') }}" 
                        :class="scrolled ? 'text-slate-650 hover:text-emerald-650' : 'text-slate-700 hover:text-emerald-700'"
                        class="text-xs font-bold uppercase tracking-wider transition-all duration-300 hidden md:flex items-center gap-1 hover:translate-x-1"
                    >
                        {{ __('spmb.staff_login') }}
                        <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Visual Light Hero Section with Ambient Glows -->
    <div class="relative pt-36 pb-28 lg:pt-52 lg:pb-40 overflow-hidden bg-gradient-to-br from-emerald-50/70 via-slate-50 to-emerald-50/30">
        <!-- Ambient Mesh Background Glows & Grid -->
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-emerald-500/10 rounded-full blur-[130px] -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-[550px] h-[550px] bg-teal-500/10 rounded-full blur-[130px] -ml-24 -mb-24"></div>
            <!-- Emerald dots watermark pattern -->
            <div class="absolute inset-0 opacity-[0.035] bg-[radial-gradient(#059669_1.5px,transparent_1.5px)] [background-size:28px_28px]"></div>
        </div>

        <div class="relative w-full px-4 sm:px-6 lg:px-8 text-center z-10">
            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-700 border border-emerald-500/20 mb-6 shadow-inner animate-fade-in">
                <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-ping"></span>
                {{ __('spmb.hero_badge') }}
            </span>
            
            <h1 class="text-4xl sm:text-6xl lg:text-7.5xl font-black text-slate-900 tracking-tight leading-[1.08] mb-6">
                {{ __('spmb.hero_title_1') }} <br> {{ __('spmb.hero_title_2') }} <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 bg-clip-text text-transparent text-glow">{{ __('spmb.hero_title_3') }}</span>
            </h1>
            
            <p class="mt-4 text-sm sm:text-base lg:text-lg text-slate-600 font-medium max-w-2xl mx-auto mb-10 opacity-90 leading-relaxed font-inter">
                {{ __('spmb.hero_desc_welcome') }}
            </p>
            
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 max-w-md mx-auto sm:max-w-none">
                <a 
                    href="{{ route('spmb.create') }}" 
                    class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-[2rem] font-bold text-base shadow-xl shadow-emerald-500/10 hover:shadow-emerald-500/25 hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 group"
                >
                    {{ __('spmb.btn_start_registration') }} 
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                    </svg>
                </a>
                <a 
                    href="#alur-pendaftaran" 
                    class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-700 rounded-[2rem] font-bold text-base transition-all duration-300 justify-center flex items-center shadow-sm hover:border-slate-300"
                >
                    {{ __('spmb.btn_learn_flow') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Section with Frosted Glass -->
    <div class="relative -mt-12 max-w-5xl mx-auto px-4 z-20">
        <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-200/60 p-8 shadow-xl shadow-slate-200/10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 md:gap-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 text-center">
            <!-- Stat 1 -->
            <div class="flex flex-col items-center justify-center p-4 group hover:scale-[1.02] transition-transform duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-3 shadow-inner group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296a3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"></path>
                    </svg>
                </div>
                <h4 class="text-3xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">A</h4>
                <p class="text-[9px] font-black text-slate-550 uppercase tracking-widest mt-1">{{ __('spmb.stat_accreditation') }}</p>
            </div>
            
            <!-- Stat 2 -->
            <div class="flex flex-col items-center justify-center p-4 pt-8 sm:pt-4 group hover:scale-[1.02] transition-transform duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-3 shadow-inner group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m0 0a8.967 8.967 0 0 1-2.307-.34 3 3 0 0 1 1.983-5.244m1.984 5.584a9.052 9.052 0 0 1-1.323-2.368m3.574-9.67a3 3 0 1 1-5.714 0m5.714 0a3 3 0 1 1 5.714 0m-5.714 0a3 3 0 1 1 5.714 0M10.5 8.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6.5 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                    </svg>
                </div>
                <h4 class="text-3xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">30+</h4>
                <p class="text-[9px] font-black text-slate-550 uppercase tracking-widest mt-1">{{ __('spmb.stat_extracurricular') }}</p>
            </div>

            <!-- Stat 3 -->
            <div class="flex flex-col items-center justify-center p-4 pt-8 md:pt-4 group hover:scale-[1.02] transition-transform duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-3 shadow-inner group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.33l-7.5-5-7.5 5V21m16.5 0H3.75"></path>
                    </svg>
                </div>
                <h4 class="text-3xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">100%</h4>
                <p class="text-[9px] font-black text-slate-550 uppercase tracking-widest mt-1">{{ __('spmb.stat_islamic') }}</p>
            </div>

            <!-- Stat 4 -->
            <div class="flex flex-col items-center justify-center p-4 pt-8 md:pt-4 group hover:scale-[1.02] transition-transform duration-300">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 mb-3 shadow-inner group-hover:bg-emerald-100 group-hover:border-emerald-200 transition-all duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.9c2.785 0 5.5-.233 8.15-.683a60.43 60.43 0 0 0-.49-6.347M12 10.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 0v8.25"></path>
                    </svg>
                </div>
                <h4 class="text-3xl font-black bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">500+</h4>
                <p class="text-[9px] font-black text-slate-550 uppercase tracking-widest mt-1">{{ __('spmb.stat_achievement') }}</p>
            </div>
        </div>
    </div>

    <!-- Step Flow Section (Alur Pendaftaran) -->
    <div id="alur-pendaftaran" class="py-24 sm:py-32 bg-gradient-to-b from-white to-slate-50/70">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <span class="text-[10px] font-black tracking-widest text-emerald-600 uppercase">4 Langkah Mudah</span>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-950 tracking-tight mt-1">{{ __('spmb.flow_title') }}</h2>
                <div class="w-16 h-1 bg-emerald-500 mx-auto mt-4 rounded-full shadow-sm"></div>
            </div>

            <!-- Connectors layout on Desktop -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                <!-- Step 1 -->
                <div class="bg-white p-7 rounded-[2rem] border border-slate-200/60 text-center transform hover:scale-[1.02] hover:shadow-xl hover:shadow-emerald-500/5 hover:border-emerald-500/20 transition-all duration-350 flex flex-col justify-between group shadow-sm">
                    <div>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-base font-black mx-auto mb-6 shadow-inner group-hover:bg-emerald-500 group-hover:text-white transition-all duration-350">1</div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">{{ __('spmb.flow_step_1_title') }}</h3>
                        <p class="text-xs text-slate-500 font-inter font-normal leading-relaxed">
                            {{ __('spmb.flow_step_1_desc') }}
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-white p-7 rounded-[2rem] border border-slate-200/60 text-center transform hover:scale-[1.02] hover:shadow-xl hover:shadow-emerald-500/5 hover:border-emerald-500/20 transition-all duration-350 flex flex-col justify-between group shadow-sm">
                    <div>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-base font-black mx-auto mb-6 shadow-inner group-hover:bg-emerald-500 group-hover:text-white transition-all duration-350">2</div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">{{ __('spmb.flow_step_2_title') }}</h3>
                        <p class="text-xs text-slate-500 font-inter font-normal leading-relaxed">
                            {{ __('spmb.flow_step_2_desc') }}
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-white p-7 rounded-[2rem] border border-slate-200/60 text-center transform hover:scale-[1.02] hover:shadow-xl hover:shadow-emerald-500/5 hover:border-emerald-500/20 transition-all duration-350 flex flex-col justify-between group shadow-sm">
                    <div>
                        <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-base font-black mx-auto mb-6 shadow-inner group-hover:bg-emerald-500 group-hover:text-white transition-all duration-350">3</div>
                        <h3 class="text-sm font-black text-slate-900 mb-3">{{ __('spmb.flow_step_3_title') }}</h3>
                        <p class="text-xs text-slate-500 font-inter font-normal leading-relaxed">
                            {{ __('spmb.flow_step_3_desc') }}
                        </p>
                    </div>
                </div>

                <!-- Step 4 (Highlighted Card) -->
                <div class="bg-emerald-50/60 p-7 rounded-[2rem] border border-emerald-200 text-center transform hover:scale-[1.02] hover:shadow-xl hover:shadow-emerald-500/5 transition-all duration-350 flex flex-col justify-between group shadow-sm">
                    <div>
                        <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-base font-black mx-auto mb-6 shadow-md">4</div>
                        <h3 class="text-sm font-black text-emerald-950 mb-3">{{ __('spmb.flow_step_4_title') }}</h3>
                        <p class="text-xs text-emerald-900/80 font-inter font-normal leading-relaxed">
                            {{ __('spmb.flow_step_4_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Accordions Section with Elegant Glass -->
    <div class="py-24 sm:py-32 bg-slate-50/70 border-t border-slate-200/40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeFaq: '' }">
            <div class="text-center mb-16">
                <span class="text-[10px] font-black tracking-widest text-emerald-600 uppercase">Tanya Jawab Umum</span>
                <h2 class="text-3xl font-black text-slate-950 tracking-tight mt-1">{{ __('spmb.faq_title') }}</h2>
                <div class="w-16 h-1 bg-emerald-550 mx-auto mt-4 rounded-full"></div>
            </div>

            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 'faq-1' ? '' : 'faq-1')"
                        class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-slate-50/50 transition-colors rtl:text-right"
                    >
                        <span class="font-extrabold text-sm text-slate-900">{{ __('spmb.faq_1_q') }}</span>
                        <span class="text-emerald-500 font-bold faq-transition" :class="activeFaq === 'faq-1' ? 'rotate-180' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </button>
                    <div x-show="activeFaq === 'faq-1'" x-collapse class="px-6 pb-5">
                        <p class="text-xs text-slate-500 leading-relaxed font-inter font-normal">
                            {{ __('spmb.faq_1_a') }}
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 'faq-2' ? '' : 'faq-2')"
                        class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-slate-50/50 transition-colors rtl:text-right"
                    >
                        <span class="font-extrabold text-sm text-slate-900">{{ __('spmb.faq_2_q') }}</span>
                        <span class="text-emerald-500 font-bold faq-transition" :class="activeFaq === 'faq-2' ? 'rotate-180' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </button>
                    <div x-show="activeFaq === 'faq-2'" x-collapse class="px-6 pb-5">
                        <p class="text-xs text-slate-500 leading-relaxed font-inter font-normal">
                            {{ __('spmb.faq_2_a') }}
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition-all duration-300">
                    <button 
                        @click="activeFaq = (activeFaq === 'faq-3' ? '' : 'faq-3')"
                        class="w-full px-6 py-5 flex justify-between items-center text-left hover:bg-slate-50/50 transition-colors rtl:text-right"
                    >
                        <span class="font-extrabold text-sm text-slate-900">{{ __('spmb.faq_3_q') }}</span>
                        <span class="text-emerald-500 font-bold faq-transition" :class="activeFaq === 'faq-3' ? 'rotate-180' : ''">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"></path></svg>
                        </span>
                    </button>
                    <div x-show="activeFaq === 'faq-3'" x-collapse class="px-6 pb-5">
                        <p class="text-xs text-slate-500 leading-relaxed font-inter font-normal">
                            {{ __('spmb.faq_3_a') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action Banner -->
    <div class="py-24 bg-gradient-to-br from-emerald-800 to-teal-850 text-center relative overflow-hidden">
        <!-- Ambient mesh light overlay -->
        <div class="absolute inset-0 opacity-[0.04] bg-[radial-gradient(#ffffff_2px,transparent_2px)] [background-size:24px_24px] z-0"></div>
        <div class="absolute top-1/2 left-1/2 w-[400px] h-[400px] bg-emerald-500/10 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2"></div>
        
        <div class="relative z-10 max-w-4xl mx-auto px-4">
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight mb-4 leading-tight">{{ __('spmb.cta_title') }}</h2>
            <p class="text-emerald-100/90 font-inter text-xs sm:text-sm max-w-md mx-auto mb-8 font-normal leading-relaxed">
                {{ __('spmb.cta_desc') }}
            </p>
            <a 
                href="{{ route('spmb.create') }}" 
                class="inline-flex px-8 py-4 bg-white text-emerald-700 hover:bg-slate-50 font-bold rounded-[2rem] text-sm shadow-xl shadow-slate-950/10 hover:scale-[1.03] active:scale-[0.98] transition-all transform duration-300"
            >
                {{ __('spmb.cta_btn') }} &rarr;
            </a>
        </div>
    </div>

    <!-- Premium Footer -->
    <footer class="bg-slate-50 text-slate-550 py-16 border-t border-slate-200/50">
        <div class="w-full px-4 sm:px-6 lg:px-8 text-center space-y-6">
            <div class="h-10 w-auto flex items-center justify-center filter grayscale opacity-60 mx-auto">
                <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain">
            </div>
            <p class="text-xs font-black uppercase tracking-widest text-slate-650">&copy; {{ date('Y') }} {{ __('public.footer_copyright') }}</p>
            <p class="text-[10px] font-inter text-slate-500 max-w-md mx-auto leading-relaxed">
                {{ __('public.footer_address') }}
            </p>
        </div>
    </footer>
</body>
</html>
