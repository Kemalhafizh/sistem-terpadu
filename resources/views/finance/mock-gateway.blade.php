<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD Secure Payment Gateway</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-100/50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white w-full max-w-md rounded-[2.5rem] shadow-2xl overflow-hidden border border-gray-100 flex flex-col h-[600px] relative">
        <!-- Header -->
        <div class="bg-primary-600 p-6 text-white text-center relative">
            <div class="absolute top-4 left-6 flex items-center">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse mr-2"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">Secure Session Active</span>
            </div>
            <h1 class="text-xl font-black mt-2 tracking-tight">Sultan Agung Payment Hub</h1>
            <p class="text-[10px] uppercase font-bold opacity-60 tracking-widest mt-1">Payment Gateway Simulation</p>
        </div>

        <!-- Order Summary -->
        <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Jumlah Tagihan</p>
                <p class="text-2xl font-black text-gray-800 tracking-tighter">Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Invoice</p>
                <p class="text-sm font-bold text-gray-600">{{ $billing->snap_token }}</p>
            </div>
        </div>

        <!-- Payment Methods -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Metode Pembayaran</p>
            
            <!-- Method Item -->
            <div class="p-4 border border-gray-100 rounded-2xl flex items-center justify-between hover:border-primary-500 hover:bg-primary-50 cursor-pointer transition-all group active:scale-95">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white shadow-sm border border-gray-50 rounded-lg flex items-center justify-center font-bold text-blue-600 text-xs">BCA</div>
                    <span class="text-sm font-bold text-gray-700">Virtual Account BCA</span>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <div class="p-4 border border-gray-100 rounded-2xl flex items-center justify-between hover:border-primary-500 hover:bg-primary-50 cursor-pointer transition-all group active:scale-95">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white shadow-sm border border-gray-50 rounded-lg flex items-center justify-center font-bold text-emerald-600 text-xs">QRIS</div>
                    <span class="text-sm font-bold text-gray-700">Gopay / ShopeePay / QRIS</span>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>

            <div class="p-4 border border-gray-100 rounded-2xl flex items-center justify-between hover:border-primary-500 hover:bg-primary-50 cursor-pointer transition-all group active:scale-95">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white shadow-sm border border-gray-50 rounded-lg flex items-center justify-center font-bold text-orange-600 text-xs">BNI</div>
                    <span class="text-sm font-bold text-gray-700">Virtual Account BNI</span>
                </div>
                <svg class="w-5 h-5 text-gray-300 group-hover:text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </div>
        </div>

        <!-- Footer Simulation Action -->
        <div class="p-6 border-t border-gray-100 bg-white space-y-3">
             <form id="payForm" action="{{ route('finance.mock-callback', $billing->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-primary-600 text-white py-4 rounded-2xl font-black text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.02] active:scale-95 transition-all">
                    SIMULASI PEMBAYARAN BERHASIL
                </button>
             </form>
             <p class="text-[10px] text-gray-400 text-center uppercase tracking-widest font-bold">Tombol ini mensimulasikan Webhook dari Bank</p>
        </div>

        <!-- Loading Overlay Concept -->
        <div id="loading" class="hidden absolute inset-0 bg-white/90 backdrop-blur-sm z-20 flex flex-col items-center justify-center p-10 text-center">
             <div class="w-12 h-12 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mb-6"></div>
             <p class="font-black text-gray-800 text-lg uppercase tracking-tight">Memproses Transaksi...</p>
             <p class="text-xs text-gray-400 mt-2">Mohon tunggu sebentar, kami sedang menghubungkan Anda ke sistem perbankan aman.</p>
        </div>
    </div>

    <script>
        document.getElementById('payForm').onsubmit = function() {
            document.getElementById('loading').classList.remove('hidden');
        };
    </script>
</body>
</html>
