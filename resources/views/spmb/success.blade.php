<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Berhasil | Sultan Agung 1 Semarang</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;850;900&family=Inter:wght@300;400;500;600;750&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .font-inter { font-family: 'Inter', sans-serif; }
        
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 2px solid #000000 !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
                padding: 20px !important;
            }
            .print-header {
                border-bottom: 2px double #000000 !important;
                padding-bottom: 15px !important;
                margin-bottom: 20px !important;
            }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Struk Card -->
    <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200/60 print-card">
        
        <!-- Header Struk (Voucher Header) -->
        <div class="bg-emerald-500 py-8 px-6 text-center text-white relative print-header">
            <!-- Confetti CSS Effect decoration -->
            <div class="absolute inset-0 z-0 opacity-15 bg-[radial-gradient(#ffffff_1px,transparent_1px)] [background-size:16px_16px] no-print"></div>

            <div class="relative z-10">
                <div class="mx-auto w-16 h-16 bg-white rounded-2xl flex items-center justify-center p-2 mb-4 shadow-lg">
                    <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain">
                </div>
                <h2 class="text-xl font-black tracking-tight uppercase leading-none">Pendaftaran Berhasil!</h2>
                <p class="text-emerald-100 mt-1.5 text-xs font-semibold leading-none">Voucher Tanda Terima SPMB Digital</p>
            </div>
        </div>

        <!-- Body Struk -->
        <div class="p-8 space-y-6">
            <div class="text-center">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Nomor Pendaftaran Anda</p>
                <div class="bg-slate-50 border-2 border-dashed border-slate-350 rounded-2xl py-5">
                    <span class="text-3xl font-black text-slate-900 tracking-tighter">{{ session('registration_number') }}</span>
                </div>
                <p class="mt-2 text-[10px] text-red-500 font-bold bg-red-50 py-1.5 px-3 rounded-lg inline-block uppercase tracking-wider no-print">
                    ⚠️ Harap screenshot / simpan nomor ini!
                </p>
            </div>

            <!-- Candidate summary info -->
            @if(isset($candidate) && $candidate)
            <div class="border-t border-b border-slate-100 py-4 space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Nama Pendaftar:</span>
                    <strong class="text-slate-800 text-right">{{ $candidate->full_name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">Asal Sekolah:</span>
                    <strong class="text-slate-800 text-right">{{ $candidate->previous_school }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400 font-medium">NISN:</span>
                    <strong class="text-slate-800 text-right">{{ $candidate->nisn ?: '-' }}</strong>
                </div>
            </div>
            @endif

            <!-- Steps Next -->
            <div class="text-xs text-blue-900 leading-relaxed bg-blue-50/60 p-5 rounded-2xl border border-blue-200/50 text-left font-inter font-medium">
                <h4 class="font-bold text-blue-900 mb-2 uppercase tracking-wide text-[10px]">Langkah Selanjutnya:</h4>
                <ul class="list-decimal list-inside space-y-1.5 text-blue-850">
                    <li>Panitia SPMB akan meninjau dan melakukan verifikasi berkas secara manual.</li>
                    <li>Status berkas kelulusan dapat dipantau di portal cek status.</li>
                    <li>Jika berkas diterima, sistem otomatis menerbitkan data akun siswa baru.</li>
                </ul>
            </div>

            <!-- Action buttons -->
            <div class="pt-4 space-y-3 no-print">
                <button 
                    onclick="window.print()"
                    class="w-full flex justify-center py-3.5 px-4 bg-slate-900 hover:bg-black text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg hover:shadow-black/25 transition-all transform hover:-translate-y-0.5"
                >
                    🖨️ Cetak Tanda Terima
                </button>
                
                <a 
                    href="{{ route('spmb.welcome') }}" 
                    class="block text-center text-xs font-bold text-slate-400 hover:text-slate-700 transition-colors pt-2"
                >
                    &larr; Kembali ke Portal Utama
                </a>
            </div>
        </div>
    </div>

</body>
</html>
