<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sabar Sebentar - Terlalu Banyak Permintaan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full text-center space-y-8 bg-white p-10 rounded-[2.5rem] shadow-2xl shadow-indigo-100 border border-gray-100 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-50 rounded-full opacity-50"></div>
        
        <!-- Icon -->
        <div class="relative inline-flex items-center justify-center">
            <div class="absolute inset-0 bg-indigo-100 rounded-full animate-ping opacity-25"></div>
            <div class="w-24 h-24 bg-indigo-900 text-white rounded-full flex items-center justify-center shadow-xl relative z-10">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="space-y-4">
            <h1 class="text-3xl font-black gradient-text">Sabar Sebentar ya!</h1>
            <p class="text-gray-500 text-sm leading-relaxed">
                Sistem mendeteksi aktivitas yang terlalu cepat dari perangkat kamu. Untuk menjaga keamanan server sekolah, akses dibatasi sementara.
            </p>
        </div>

        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100">
            <p class="text-[10px] font-black text-amber-700 uppercase tracking-widest mb-1">Status Keamanan</p>
            <p class="text-xs font-bold text-amber-800">Akses Terkunci Sementara (Rate Limited)</p>
        </div>

        <div class="pt-6 flex flex-col space-y-3">
            <a href="/" class="px-8 py-4 bg-indigo-900 text-white rounded-2xl font-black text-sm shadow-lg hover:bg-indigo-800 transition-all">
                Coba Lagi Nanti
            </a>
            <p class="text-[10px] text-gray-400">Jika kamu merasa ini kesalahan, silakan hubungi <span class="font-bold">IT Helpdesk SMA Islam Sultan Agung 1</span>.</p>
        </div>
    </div>
</body>
</html>
