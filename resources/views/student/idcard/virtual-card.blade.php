@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
        <h1 class="text-xl font-black text-gray-900 dark:text-white">Kartu Pelajar Digital (Digital Student ID)</h1>
        <p class="text-xs text-gray-500 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>

    <!-- Virtual Card UI -->
    <div class="bg-gradient-to-br from-indigo-900 via-sky-900 to-emerald-900 text-white rounded-3xl p-6 shadow-2xl space-y-6 border border-white/20">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-300">SMA ISA 1 SEMARANG</p>
                <p class="text-xs font-semibold text-gray-200">KARTU TANDA PELAJAR DIGITAL</p>
            </div>
            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-300 font-bold text-[10px] rounded-full border border-emerald-400/30">NFC READY</span>
        </div>

        <div class="flex items-center gap-4 py-2">
            <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center font-black text-xl text-emerald-400 border border-white/20 shadow-inner">
                {{ substr(auth()->user()->name ?? 'S', 0, 1) }}
            </div>
            <div>
                <h3 class="text-lg font-black tracking-wide">{{ auth()->user()->name ?? 'Siswa' }}</h3>
                <p class="text-xs text-gray-300 font-mono">NISN: {{ auth()->user()->nisn ?? '3374019284' }}</p>
                <p class="text-[11px] text-emerald-300 font-semibold mt-0.5">Kelas XI IPA 1</p>
            </div>
        </div>

        <div class="bg-white/10 p-4 rounded-2xl border border-white/10 text-center space-y-2">
            <p class="text-[10px] font-bold text-gray-300 uppercase">QR Presensi & Access Gateway</p>
            <div class="bg-white p-3 rounded-xl inline-block shadow-lg">
                <div class="w-24 h-24 bg-gray-900 rounded-lg flex items-center justify-center text-white font-mono text-[10px] text-center p-1">
                    {{ $card->card_hash ?? 'SECURE-QR-HASH' }}
                </div>
            </div>
            <p class="text-[9px] text-gray-400 font-mono">Kode Keamanan Terenskripsi AES-256</p>
        </div>
    </div>
</div>
@endsection
