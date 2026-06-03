<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Sultan Agung 1</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-[#FCFCFD] text-gray-900 overflow-hidden">
    <div class="min-h-screen flex items-center justify-center p-6 relative">
        <!-- Abstract Decorations -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-50 rounded-full blur-[100px] opacity-60 -mr-60 -mt-60"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-emerald-50 rounded-full blur-[100px] opacity-40 -ml-40 -mb-40"></div>

        <div class="max-w-xl w-full text-center relative z-10">
            <div class="mb-12 inline-block p-1 bg-white rounded-[2rem] shadow-xl shadow-primary-900/5">
                <div class="bg-primary-600 w-24 h-24 rounded-[1.8rem] flex items-center justify-center text-white shadow-lg">
                    @yield('icon')
                </div>
            </div>

            <h1 class="text-9xl font-black text-primary-900 tracking-tighter mb-4">@yield('code')</h1>
            <h2 class="text-3xl font-black text-gray-800 tracking-tight mb-6">@yield('message')</h2>
            <p class="text-gray-400 font-medium mb-10 px-8">
                @yield('description')
            </p>

            <div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center">
                <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="px-8 py-4 bg-white border border-gray-100 rounded-2xl font-black text-sm text-gray-600 shadow-sm hover:bg-gray-50 transition-all">
                    KEMBALI KE SEBELUMNYA
                </a>
                <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.05] active:scale-95 transition-all">
                    MENU UTAMA DASHBOARD
                </a>
            </div>

            <div class="mt-20 pt-10 border-t border-gray-100/50">
                <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">
                    Sultan Agung 1 Semarang Integrated System
                </p>
            </div>
        </div>
    </div>
</body>
</html>
