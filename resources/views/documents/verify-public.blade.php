<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Keabsahan Dokumen Resmi — {{ school_name() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-gray-100 min-h-screen flex items-center justify-center p-4 antialiased font-sans">

    <div class="max-w-xl w-full bg-slate-800 rounded-3xl border border-slate-700/80 shadow-2xl p-8 space-y-6">

        <!-- Header Brand -->
        <div class="text-center border-b border-slate-700 pb-6 space-y-2">
            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-xl font-mono text-[10px] font-bold uppercase tracking-wider">
                PORTAL VERIFIKASI KEABSAHAN DOKUMEN PUBLIK
            </span>
            <h1 class="text-xl font-black text-white tracking-tight">{{ mb_strtoupper(school_name()) }}</h1>
            <p class="text-xs text-gray-400">Pusat Validasi Tanda Tangan Digital & E-Stamp Kriptografi SHA-256</p>
        </div>

        @if($verification['is_valid'])
            <!-- Valid Document Banner -->
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl flex items-center gap-4">
                <div class="p-3 bg-emerald-500/20 text-emerald-400 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-black text-emerald-400 uppercase tracking-wide">DOKUMEN DIJAMIN ABSAH & ASLI</h2>
                    <p class="text-xs text-emerald-200/80">Dokumen terverifikasi resmi diterbitkan oleh {{ school_name() }}.</p>
                </div>
            </div>

            <!-- Metadata Details Table -->
            <div class="space-y-3 text-xs font-medium divide-y divide-slate-700/60">
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">Jenis Dokumen:</span>
                    <span class="font-bold text-white">{{ $verification['document_type'] }}</span>
                </div>
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">ID Registrasi Dokumen:</span>
                    <span class="font-mono text-indigo-400 font-bold">#{{ $verification['document_id'] }}</span>
                </div>
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">Nama Penandatangan:</span>
                    <span class="font-bold text-white">{{ $verification['signer_name'] }}</span>
                </div>
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">Jabatan Resmi:</span>
                    <span class="font-mono text-gray-300">{{ $verification['signer_role'] }}</span>
                </div>
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">Waktu Pembubuhan E-Stamp:</span>
                    <span class="font-mono text-gray-300">{{ $verification['signed_at'] }}</span>
                </div>
                <div class="pt-2 flex justify-between">
                    <span class="text-gray-400">Kode Unik QR:</span>
                    <span class="font-mono text-indigo-400 font-bold">{{ $verification['qr_code'] }}</span>
                </div>
                <div class="pt-2">
                    <span class="text-gray-400 block mb-1">Cryptographic SHA-256 Hash Digest:</span>
                    <span class="bg-slate-900 px-3 py-2 rounded-xl text-[10px] font-mono text-gray-300 block truncate" title="{{ $verification['signature_hash'] }}">
                        {{ $verification['signature_hash'] }}
                    </span>
                </div>
            </div>
        @else
            <!-- Invalid / Not Found Banner -->
            <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl flex items-center gap-4">
                <div class="p-3 bg-rose-500/20 text-rose-400 rounded-xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-black text-rose-400 uppercase tracking-wide">VERIFIKASI GAGAL / TIDAK DITEMUKAN</h2>
                    <p class="text-xs text-rose-200/80">{{ $verification['message'] }}</p>
                </div>
            </div>
        @endif

        <div class="pt-4 border-t border-slate-700 text-center text-[10px] font-mono text-gray-500 space-y-1">
            <p>{{ school_foundation_name() }}</p>
            <p>Sistem Terpadu Akademik & Manajemen Sekolah (SISTA) &copy; 2026</p>
        </div>

    </div>

</body>
</html>
