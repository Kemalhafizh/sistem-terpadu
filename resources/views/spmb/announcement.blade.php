<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hasil Seleksi SPMB | SMA Islam Sultan Agung 1 Semarang</title>
    
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
    </style>
</head>
<body class="antialiased text-slate-800 min-h-screen flex flex-col justify-between">

    <!-- Navbar -->
    <nav class="bg-white/75 backdrop-blur-md border-b border-slate-200/50 py-4 shadow-sm fixed w-full z-50">
        <div class="w-full px-4 flex justify-between items-center h-12">
            <div class="flex items-center">
                <a href="{{ route('spmb.welcome') }}" class="flex items-center text-slate-400 hover:text-emerald-600 transition-colors mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path></svg>
                </a>
                <div class="h-10 w-10 flex items-center justify-center p-1 bg-white rounded-xl shadow-sm border border-slate-200/60 mr-3">
                    <img src="{{ asset('images/logo-kotak.png') }}" alt="Sultan Agung 1" class="h-full object-contain">
                </div>
                <div>
                    <h1 class="font-black text-lg text-slate-900 leading-none">Sultan Agung 1</h1>
                    <p class="text-[9px] text-slate-400 font-bold uppercase mt-1">Hasil Seleksi Murid Baru</p>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center pt-32 pb-16 px-4">
        <div class="max-w-xl w-full">
            
            <div class="text-center mb-8">
                <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-wider mb-3">Pengumuman</span>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight">Pencarian Hasil Seleksi</h2>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-2">
                    Masukkan nomor pendaftaran atau NISN Anda untuk melihat status kelulusan seleksi murid baru.
                </p>
            </div>

            <!-- Search Box -->
            <div class="bg-white p-6 shadow-xl rounded-3xl border border-slate-200/60 mb-6">
                <form action="{{ route('spmb.announcement') }}" method="GET" class="space-y-4">
                    <div>
                        <label for="search" class="block font-bold text-xs text-slate-400 uppercase tracking-wider mb-1.5">No. Pendaftaran / NISN</label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                name="search" 
                                id="search" 
                                value="{{ request('search') }}"
                                required
                                placeholder="Misal: SPMB-2026-0001 atau NISN Anda"
                                class="block w-full bg-slate-50 border border-slate-250 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            >
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl transition-all">Cari</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Search Result -->
            @if(request()->filled('search'))
                @if($registration)
                    <div class="bg-white p-6 shadow-xl rounded-3xl border border-slate-200/60 transition-all">
                        <div class="text-center pb-6 border-b border-slate-100">
                            <div class="h-16 w-16 mx-auto rounded-full bg-slate-50 border border-slate-150 flex items-center justify-center mb-3">
                                @if($registration->photo_path)
                                    <img src="{{ asset('storage/' . $registration->photo_path) }}" alt="Foto" class="h-full w-full object-cover rounded-full">
                                @else
                                    <span class="text-2xl"></span>
                                @endif
                            </div>
                            <h3 class="text-base font-black text-slate-800 leading-none">{{ $registration->full_name }}</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1.5">{{ $registration->registration_number }}</p>
                        </div>

                        <div class="py-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4 text-xs">
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Jalur Seleksi</span>
                                    <strong class="text-slate-700">{{ $registration->spmbTrack->name ?? 'Reguler' }}</strong>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Skor Akhir / Peringkat</span>
                                    <strong class="text-slate-700">{{ $registration->total_score ?? '-' }} (Rank #{{ $registration->rank ?? '-' }})</strong>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Sekolah Asal</span>
                                    <strong class="text-slate-700">{{ $registration->previous_school }}</strong>
                                </div>
                                <div>
                                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider">Tahun Ajaran</span>
                                    <strong class="text-slate-700">{{ $registration->academicYear->year ?? '-' }}</strong>
                                </div>
                            </div>

                            <hr class="border-slate-100" />

                            <div class="text-center py-2">
                                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status Hasil Seleksi</span>
                                @if($registration->status === 'accepted')
                                    <div class="inline-flex px-6 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 font-black rounded-2xl text-sm uppercase tracking-wider animate-pulse">
                                         DITERIMA / PASSED
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-3 max-w-sm mx-auto leading-relaxed">
                                        Selamat! Anda dinyatakan lulus seleksi masuk SMA Islam Sultan Agung 1 Semarang. Silakan melakukan proses daftar ulang.
                                    </p>
                                    @if(!$registration->has_reregistered)
                                        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs font-semibold text-amber-800 text-center">
                                             Status: Belum Daftar Ulang. Silakan menghubungi panitia SPMB untuk pembayaran uang pangkal.
                                        </div>
                                    @else
                                        <div class="mt-4 p-4 bg-emerald-50 border border-emerald-250 rounded-2xl text-xs font-semibold text-emerald-800 text-center">
                                             Daftar Ulang Selesai. Akun siswa Anda sedang diproses.
                                        </div>
                                    @endif
                                @elseif($registration->status === 'rejected')
                                    <div class="inline-flex px-6 py-3 bg-red-50 border border-red-200 text-red-700 font-black rounded-2xl text-sm uppercase tracking-wider">
                                         TIDAK DITERIMA
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-3 max-w-sm mx-auto leading-relaxed">
                                        Terima kasih atas partisipasi Anda. Jangan berkecil hati dan tetap semangat belajar untuk meraih cita-cita Anda.
                                    </p>
                                @else
                                    <div class="inline-flex px-6 py-3 bg-blue-50 border border-blue-200 text-blue-700 font-black rounded-2xl text-sm uppercase tracking-wider">
                                        ⏳ PROSES VERIFIKASI / SELEKSI
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-3 max-w-sm mx-auto leading-relaxed">
                                        Data Anda sedang dalam proses verifikasi berkas dan pengolahan nilai seleksi. Harap periksa secara berkala.
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex justify-center gap-2">
                            <a href="{{ route('spmb.print-card', $registration) }}" target="_blank" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-xl transition-all">Cetak Bukti Kartu Pendaftar</a>
                        </div>
                    </div>
                @else
                    <div class="bg-white p-8 shadow-xl rounded-3xl border border-slate-200/60 text-center">
                        <span class="text-4xl"></span>
                        <h3 class="text-sm font-black text-slate-800 mt-2">Data Tidak Ditemukan</h3>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">Pastikan nomor pendaftaran yang Anda masukkan benar dan sesuai dengan format SPMB-YYYY-XXXX.</p>
                    </div>
                @endif
            @endif

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 border-t border-slate-200/50 py-6 text-center">
        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">&copy; {{ date('Y') }} SMA Islam Sultan Agung 1 Semarang. All Rights Reserved.</p>
    </footer>

</body>
</html>
