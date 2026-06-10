<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sultan Agung 1</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#FCFCFD] text-gray-900 overflow-hidden" style="background-color: #FCFCFD; color: #111827;">
    <div class="min-h-screen flex items-center justify-center p-6 relative" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; position: relative;">
        <!-- Abstract Decorations -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-50 rounded-full blur-[100px] opacity-60 -mr-60 -mt-60" style="position: absolute; top: 0; right: 0; width: 500px; height: 500px; background-color: #ecfdf5; border-radius: 9999px; filter: blur(100px); opacity: 0.6; margin-right: -15rem; margin-top: -15rem;"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-emerald-50 rounded-full blur-[100px] opacity-40 -ml-40 -mb-40" style="position: absolute; bottom: 0; left: 0; width: 400px; height: 400px; background-color: #ecfdf5; border-radius: 9999px; filter: blur(100px); opacity: 0.4; margin-left: -10rem; margin-bottom: -10rem;"></div>

        <div class="max-w-xl w-full text-center relative z-10" style="max-width: 36rem; width: 100%; text-align: center; position: relative; z-index: 10;">
            <div class="mb-12 inline-block p-1 bg-white rounded-[2rem] shadow-xl shadow-primary-900/5" style="background-color: #ffffff;">
                <div class="bg-primary-600 w-24 h-24 rounded-[1.8rem] flex items-center justify-center text-white shadow-lg" style="width: 6rem; height: 6rem; background-color: #059669; display: flex; align-items: center; justify-content: center; color: #ffffff; border-radius: 1.8rem; margin: 0 auto;">
                    @yield('icon')
                </div>
            </div>

            <h1 class="text-9xl font-black text-primary-900 tracking-tighter mb-4" style="font-size: 8rem; font-weight: 900; color: #064e3b; margin-bottom: 1rem; line-height: 1;">@yield('code')</h1>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight mb-6" style="font-size: 1.875rem; font-weight: 900; color: #1f2937; margin-bottom: 1.5rem;">@yield('message')</h2>
            <p class="text-gray-400 font-medium mb-10 px-8" style="color: #9ca3af; font-weight: 500; margin-bottom: 2.5rem; padding-left: 2rem; padding-right: 2rem;">
                @yield('description')
            </p>

            @section('buttons')
            <div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center" style="display: flex; gap: 1rem; justify-content: center; align-items: center;">
                <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-black text-sm text-gray-600 shadow-sm hover:bg-gray-50 transition-all" style="padding: 1rem 2rem; background-color: #ffffff; border: 1px solid #f3f4f6; border-radius: 1rem; color: #4b5563; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
                    KEMBALI KE SEBELUMNYA
                </a>
                <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.05] active:scale-95 transition-all" style="padding: 1rem 2rem; background-color: #059669; color: #ffffff; border-radius: 1rem; text-decoration: none; font-weight: 800; font-size: 0.875rem;">
                    MENU UTAMA DASHBOARD
                </a>
            </div>
            @show

            <div class="mt-20 pt-10 border-t border-gray-100/50" style="margin-top: 5rem; padding-top: 2.5rem; border-top: 1px solid rgba(243, 244, 246, 0.5);">
                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]" style="font-size: 10px; font-weight: 900; color: #d1d5db; text-transform: uppercase; letter-spacing: 0.3em;">
                    Sultan Agung 1 Semarang Integrated System
                </p>
            </div>
        </div>
    </div>
</body>
</html>
