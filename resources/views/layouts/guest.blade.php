<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appearance->app_name }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ $appearance->favicon_path ? asset('storage/' . $appearance->favicon_path) : asset('images/logo-kotak.png') }}">

    <!-- Fonts - Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;850;900&family=Inter:wght@300;400;500;600;750;800&family={{ str_replace(' ', '+', $appearance->font_family) }}:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Dynamic Appearance Branding Styles -->
    <style>
        :root {
            --color-primary-500: {{ $appearance->primary_color }} !important;
            --color-primary-600: {{ $appearance->secondary_color }} !important;
            --color-primary-700: {{ $appearance->secondary_color }} !important;
            --color-primary-800: {{ $appearance->sidebar_color }} !important;
            --color-primary-900: {{ $appearance->sidebar_color }} !important;
            --color-primary-950: {{ $appearance->sidebar_color }} !important;
            
            --font-sans: '{{ $appearance->font_family }}', 'Outfit', 'Inter', sans-serif !important;
        }
        
        body { font-family: var(--font-sans) !important; }
        .font-inter { font-family: 'Inter', sans-serif; }
        
        /* Subtle Islamic Geometric Pattern Overlay */
        .islamic-pattern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cpath d='M30 0 L45 15 L60 30 L45 45 L30 60 L15 45 L0 30 L15 15 Z' fill='none' stroke='%23059669' stroke-width='0.4' stroke-opacity='0.08'/%3E%3Cpath d='M30 10 L40 20 L50 30 L40 40 L30 50 L20 40 L10 30 L20 20 Z' fill='none' stroke='%23d97706' stroke-width='0.3' stroke-opacity='0.08'/%3E%3Ccircle cx='30' cy='30' r='5' fill='none' stroke='%23059669' stroke-width='0.4' stroke-opacity='0.08'/%3E%3C/svg%3E");
        }

        /* Custom Background Image Override if uploaded */
        @if($appearance->login_background_path)
        .login-bg-custom {
            background-image: url("{{ asset('storage/' . $appearance->login_background_path) }}") !important;
            background-size: cover !important;
            background-position: center !important;
        }
        @endif

        /* Custom CSS Override */
        {!! $appearance->custom_css !!}
    </style>
</head>
<body class="font-sans text-slate-800 antialiased h-full overflow-hidden bg-slate-50">
    
    <!-- Outer Container -->
    <div class="min-h-screen flex h-full">
        
        <!-- Left Side: Bright Emerald Brand Showcase (Hidden on Mobile) -->
        <div class="hidden lg:flex lg:w-[45%] bg-gradient-to-br from-emerald-50 via-emerald-100/70 to-teal-50/50 relative overflow-hidden items-center justify-center p-12 select-none border-r border-slate-200/50 {{ $appearance->login_background_path ? 'login-bg-custom' : '' }}">
            <!-- Decorative Islamic Geometric Pattern Overlay -->
            @if(!$appearance->login_background_path)
            <div class="absolute inset-0 islamic-pattern"></div>
            @endif
            
            <!-- Soft Floating Lighting Orb (Left Pane) -->
            <div class="absolute top-10 left-10 w-[250px] h-[250px] bg-emerald-300/10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-10 right-10 w-[200px] h-[200px] bg-amber-400/10 rounded-full blur-[90px]"></div>
 
            <div class="relative z-10 w-full max-w-md space-y-8 bg-white/40 dark:bg-slate-900/40 backdrop-blur-md p-6 rounded-3xl border border-white/20">
                
                <!-- Floating Glass Brand Badge -->
                <div class="inline-flex items-center gap-4 p-3 bg-white/90 backdrop-blur-md rounded-2xl border border-emerald-100 shadow-md">
                    <img src="{{ $appearance->logo_path ? asset('storage/' . $appearance->logo_path) : asset('images/logo-kotak.png') }}" class="w-10 h-10 object-contain" alt="Logo SISTA">
                    <div class="flex flex-col pr-2">
                        <span class="text-[9px] font-black uppercase tracking-widest text-emerald-700 leading-none mb-0.5">Portal Akademik</span>
                        <span class="text-xs font-black text-slate-900 leading-none">{{ $appearance->app_name }}</span>
                    </div>
                </div>
                
                <!-- Headline -->
                <div class="space-y-4">
                    <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-widest bg-emerald-600/10 text-emerald-700 border border-emerald-500/20">
                        Enterprise Portal Gate
                    </span>
                    <h1 class="text-4xl lg:text-5xl font-black text-slate-900 leading-[1.15] tracking-tight">
                        Sistem Terpadu <br> 
                        <span class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 bg-clip-text text-transparent">{{ $appearance->app_name }}</span>
                    </h1>
                    <p class="text-sm font-inter text-slate-650 leading-relaxed">
                        Gerbang administrasi digital sekolah berstandar enterprise. Efisiensi, akuntabilitas, dan transparansi pelayanan kependidikan dalam satu platform terintegrasi.
                    </p>
                </div>
 
                <!-- Point of Value Checkmarks -->
                <div class="space-y-3 font-inter">
                    <div class="flex items-center gap-3 text-slate-700">
                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-600/10 flex items-center justify-center text-emerald-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <span class="text-xs font-semibold">Smart Academic System (Real-time & Akurat)</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-700">
                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-600/10 flex items-center justify-center text-emerald-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <span class="text-xs font-semibold">Otomatisasi & Rekonsiliasi Transaksi SPP/Keuangan</span>
                    </div>
                    <div class="flex items-center gap-3 text-slate-700">
                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-600/10 flex items-center justify-center text-emerald-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        </div>
                        <span class="text-xs font-semibold">Platform Terenkripsi & Akuntabel Berstandar Akreditasi</span>
                    </div>
                </div>
 
                <!-- Hero Image Holder with Premium Frame -->
                @if(!$appearance->login_background_path)
                <div class="relative bg-white p-3.5 rounded-[2.5rem] shadow-xl border border-slate-150/80 group">
                    <div class="relative rounded-[2rem] overflow-hidden aspect-[4/3] bg-slate-100 shadow-inner">
                        <img src="{{ asset('images/login-hero.png') }}" alt="School Building" class="w-full h-full object-cover transform group-hover:scale-[1.03] transition-transform duration-1000 opacity-95">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 via-transparent to-transparent"></div>
                    </div>
                    
                    <!-- Frosted Tag Box inside the image -->
                    <div class="absolute bottom-7 left-7 right-7 bg-white/95 backdrop-blur-md border border-slate-150 rounded-2xl p-4 flex justify-between items-center shadow-lg">
                        <div>
                            <p class="text-[9px] font-black text-slate-800 uppercase tracking-widest leading-none mb-1">Kampus Utama</p>
                            <p class="text-[11px] font-black text-emerald-600 leading-none">Building Excellence Since 1970</p>
                        </div>
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Semarang</span>
                    </div>
                </div>
                @endif
                
            </div>
            
            <!-- Bottom school brand -->
            <div class="absolute bottom-8 left-12 right-12 flex justify-between text-[9px] font-black text-slate-450 uppercase tracking-widest">
                <span>{{ $appearance->app_name }}</span>
                <span>v2.1</span>
            </div>
        </div>
 
        <!-- Right Side: Clean White SaaS Login Gate -->
        <div class="w-full lg:w-[55%] flex items-center justify-center p-6 sm:p-12 md:p-20 bg-gradient-to-br from-white via-slate-50 to-emerald-50/10 relative">
            
            <!-- Ambient Glow Orbs in the background -->
            <div class="absolute top-0 right-0 w-[350px] h-[350px] bg-emerald-500/5 rounded-full blur-[120px] -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-amber-400/5 rounded-full blur-[100px] -ml-20 -mb-20"></div>
 
            <div class="w-full max-w-md relative z-10">
                
                <!-- Mobile Logo Header (Hidden on Desktop) -->
                <div class="lg:hidden mb-8 flex flex-col items-center text-center">
                    <div class="inline-flex p-3 bg-white border border-emerald-100 rounded-2xl shadow-md mb-3">
                        <img src="{{ $appearance->logo_path ? asset('storage/' . $appearance->logo_path) : asset('images/logo-kotak.png') }}" class="w-12 h-12 object-contain" alt="Logo SISTA">
                    </div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none mb-1">Sistem Terpadu</h2>
                    <p class="text-xs font-black text-emerald-600 uppercase tracking-widest">{{ $appearance->app_name }}</p>
                </div>
 
                <!-- Greeting Header (Desktop) -->
                <div class="hidden lg:block mb-8 text-left">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight leading-none mb-2">Selamat Datang</h2>
                    <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest">Silakan Masuk Untuk Mengakses Layanan</p>
                </div>
 
                <!-- Main Login Card with Clean styling -->
                <div class="bg-white border border-slate-100 shadow-2xl p-6 sm:p-10 rounded-[2.5rem] relative overflow-hidden">
                    <!-- Subtle top decorative brand stripe -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                    
                    {{ $slot }}
                </div>
 
                <!-- Footer Copyright -->
                <div class="mt-10 text-center">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        &copy; {{ date('Y') }} {{ $appearance->footer_text }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>

