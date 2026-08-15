<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {!! app()->getLocale() === 'ar' ? 'dir="rtl"' : 'dir="ltr"' !!} class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appearance->app_name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $appearance->favicon_path ? asset('storage/' . $appearance->favicon_path) : asset('images/logo-kotak.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() === 'ar')
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;850;900&family=Inter:wght@300;400;500;600;750;800&family={{ str_replace(' ', '+', $appearance->font_family) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dynamic Appearance Branding Styles -->
    @php
        $brandingStyles = "
        :root {
            --color-primary-500: {$appearance->primary_color} !important;
            --color-primary-600: {$appearance->secondary_color} !important;
            --color-primary-700: {$appearance->secondary_color} !important;
            --color-primary-800: {$appearance->sidebar_color} !important;
            --color-primary-900: {$appearance->sidebar_color} !important;
            --color-primary-950: {$appearance->sidebar_color} !important;
            --font-sans: " . (app()->getLocale() === 'ar' ? "'Cairo', 'Amiri', sans-serif" : "'{$appearance->font_family}', 'Outfit', 'Inter', sans-serif") . " !important;
        }
        body { font-family: var(--font-sans) !important; }
        .font-inter { font-family: " . (app()->getLocale() === 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif") . "; }
        .islamic-pattern {
            background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cpath d='M30 0 L45 15 L60 30 L45 45 L30 60 L15 45 L0 30 L15 15 Z' fill='none' stroke='%23059669' stroke-width='0.4' stroke-opacity='0.08'/%3E%3Cpath d='M30 10 L40 20 L50 30 L40 40 L30 50 L20 40 L10 30 L20 20 Z' fill='none' stroke='%23d97706' stroke-width='0.3' stroke-opacity='0.08'/%3E%3Ccircle cx='30' cy='30' r='5' fill='none' stroke='%23059669' stroke-width='0.4' stroke-opacity='0.08'/%3E%3C/svg%3E\");
        }
        ";
    @endphp
    {!! '<' . 'style>' . $brandingStyles . '</' . 'style>' !!}

    <!-- Custom CSS Override -->
    @if($appearance->custom_css)
        {!! '<' . 'style>' . $appearance->custom_css . '</' . 'style>' !!}
    @endif
</head>
<body class="font-sans text-slate-800 antialiased min-h-screen bg-slate-50/40 flex items-center justify-center p-4 sm:p-6 md:p-8 relative">
    
    <!-- Language Switcher Bar (Top Float) -->
    <div class="fixed top-4 end-4 z-50" x-data="{ langOpen: false }">
        <div class="relative">
            <button @click="langOpen = !langOpen" class="px-3 py-1.5 rounded-full bg-white/90 dark:bg-gray-800/90 backdrop-blur-md shadow-md border border-slate-200/80 dark:border-gray-700 text-xs font-bold text-slate-700 dark:text-gray-200 hover:bg-white flex items-center gap-1.5 transition-all duration-200">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 2.16-.452 4.28-1.313 6.25L9.302 6.83a17.965 17.965 0 002.39-2.203H12.75z"/></svg>
                <span class="uppercase tracking-wider">{{ app()->getLocale() }}</span>
            </button>
            <div x-show="langOpen" @click.away="langOpen = false" class="absolute end-0 mt-2 w-36 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-slate-100 dark:border-gray-700 py-1.5 z-50 overflow-hidden" style="display:none;">
                <form action="{{ route('preferences.locale', 'id') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-start px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-between {{ app()->getLocale() === 'id' ? 'text-emerald-600 font-bold' : 'text-slate-600 dark:text-gray-300' }}">
                        <span>Indonesia</span>
                        @if(app()->getLocale() === 'id')
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                </form>
                <form action="{{ route('preferences.locale', 'en') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-start px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-between {{ app()->getLocale() === 'en' ? 'text-emerald-600 font-bold' : 'text-slate-600 dark:text-gray-300' }}">
                        <span>English</span>
                        @if(app()->getLocale() === 'en')
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                </form>
                <form action="{{ route('preferences.locale', 'ar') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-start px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 flex items-center justify-between {{ app()->getLocale() === 'ar' ? 'text-emerald-600 font-bold' : 'text-slate-600 dark:text-gray-300' }}">
                        <span>العربية</span>
                        @if(app()->getLocale() === 'ar')
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Background Islamic pattern overlay -->
    <div class="absolute inset-0 islamic-pattern opacity-[0.06] pointer-events-none"></div>

    <!-- Soft glowing background orbs -->
    <div class="absolute top-[-10%] left-[-10%] w-112.5 h-112.5 bg-emerald-100/40 rounded-full blur-[130px] pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-100 h-100 bg-teal-100/30 rounded-full blur-[120px] pointer-events-none"></div>

    <!-- Main Centered Floating Container Card -->
    <div class="relative z-10 w-full max-w-5xl bg-white shadow-2xl rounded-4xl overflow-hidden flex flex-col lg:flex-row min-h-155 border border-slate-100/80">
        
        <!-- Left Side: Image Panel (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-[48%] relative overflow-hidden flex-col justify-between p-10 select-none bg-slate-900">
            <!-- Full bleed background hero image -->
            @if($appearance->login_background_path)
                <img src="{{ asset('storage/' . $appearance->login_background_path) }}" class="absolute inset-0 w-full h-full object-cover pointer-events-none" alt="Custom Background">
            @else
                <img src="{{ asset('images/login-hero.png') }}" class="absolute inset-0 w-full h-full object-cover opacity-85 pointer-events-none" alt="Hero Background">
            @endif
            
            <!-- Dark gradient overlay to ensure text readability -->
            <div class="absolute inset-0 bg-linear-to-t from-slate-950 via-slate-900/30 to-slate-950/45"></div>

            <!-- Content overlay -->
            <div class="relative z-10 h-full flex flex-col justify-between">
                
                <!-- Floating Glass Brand Badge -->
                <div class="self-start inline-flex items-center gap-3 p-2 bg-slate-900/80 backdrop-blur-md rounded-2xl border border-white/8 shadow-md">
                    <div class="p-1 bg-white/10 rounded-xl">
                        <img src="{{ $appearance->logo_path ? asset('storage/' . $appearance->logo_path) : asset('images/logo-kotak.png') }}" class="w-8 h-8 object-contain" alt="Logo SISTA">
                    </div>
                    <div class="flex flex-col pr-3">
                        <span class="text-[9px] font-black uppercase tracking-widest text-emerald-400 leading-none mb-1">Portal Akademik</span>
                        <span class="text-xs font-bold text-white leading-none">{{ $appearance->app_name }}</span>
                    </div>
                </div>

                <!-- Headline & Value Points -->
                <div class="space-y-6">
                    <div class="space-y-3">
                        <span class="inline-flex px-3 py-1 rounded-full text-[9px] font-extrabold uppercase tracking-widest bg-emerald-500/20 text-emerald-350 border border-emerald-500/30">
                            Enterprise Portal Gate
                        </span>
                        <h1 class="text-3xl font-extrabold text-white leading-tight tracking-tight">
                            @if(str_starts_with(strtolower($appearance->app_name), 'sistem terpadu'))
                                Sistem Terpadu <br> 
                                <span class="bg-linear-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                                    {{ trim(substr($appearance->app_name, strlen('sistem terpadu'))) }}
                                </span>
                            @else
                                Sistem Terpadu <br> 
                                <span class="bg-linear-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                                    {{ $appearance->app_name }}
                                </span>
                            @endif
                        </h1>
                        <p class="text-xs font-inter text-slate-300 leading-relaxed max-w-sm">
                            Gerbang administrasi digital sekolah berstandar enterprise. Efisiensi, akuntabilitas, dan transparansi pelayanan kependidikan dalam satu platform terintegrasi.
                        </p>
                    </div>

                    <!-- Point of Value Checkmarks -->
                    <div class="space-y-3 font-inter">
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/30">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </div>
                            <span class="text-xs font-medium tracking-wide">Smart Academic System (Real-time & Akurat)</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/90">
                            <div class="shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-emerald-400 border border-emerald-500/30">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            </div>
                            <span class="text-xs font-medium tracking-wide">Otomatisasi & Rekonsiliasi Transaksi SPP</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Tag Box -->
                <div class="flex justify-between items-center text-[8px] font-black text-slate-400 uppercase tracking-widest gap-4">
                    <span>{{ $appearance->app_name }}</span>
                    <span>v2.1</span>
                </div>

            </div>
        </div>
 
        <!-- Right Side: Clean White Login Gate -->
        <div class="w-full lg:w-[52%] flex flex-col justify-between p-8 sm:p-12 md:p-14 bg-white relative">
            
            <!-- Mobile Logo Header (Hidden on Desktop) -->
            <div class="lg:hidden mb-8 flex flex-col items-center text-center">
                <div class="inline-flex p-3 bg-slate-50 border border-slate-100 rounded-2xl shadow-sm mb-4">
                    <img src="{{ $appearance->logo_path ? asset('storage/' . $appearance->logo_path) : asset('images/logo-kotak.png') }}" class="w-12 h-12 object-contain" alt="Logo SISTA">
                </div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight leading-none mb-1.5">Sistem Terpadu</h2>
                <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">
                    @if(str_starts_with(strtolower($appearance->app_name), 'sistem terpadu'))
                        {{ trim(substr($appearance->app_name, strlen('sistem terpadu'))) }}
                    @else
                        {{ $appearance->app_name }}
                    @endif
                </p>
            </div>
 
            <!-- Form content area -->
            <div class="my-auto space-y-6">
                <!-- Greeting Header (Desktop) -->
                <div class="hidden lg:block text-start mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight mb-2">{{ __('Masuk Akun') }}</h2>
                    <p class="text-xs text-slate-400 font-medium leading-normal">{{ __('Silakan masuk untuk mengakses layanan portal akademik.') }}</p>
                </div>
 
                <!-- Slot containing the login form -->
                <div class="relative">
                    {{ $slot }}
                </div>
            </div>
 
            <!-- Footer Copyright -->
            <div class="mt-8 text-center lg:text-left">
                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">
                    @if(str_contains($appearance->footer_text, '©') || str_contains($appearance->footer_text, '&copy;') || preg_match('/\b\d{4}\b/', $appearance->footer_text))
                        {{ $appearance->footer_text }}
                    @else
                        &copy; {{ date('Y') }} {{ $appearance->footer_text }}
                    @endif
                </p>
            </div>
        </div>
 
    </div>
</body>
</html>>

