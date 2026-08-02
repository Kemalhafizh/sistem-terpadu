@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
        <h1 class="text-xl font-black text-gray-900 dark:text-white">Dashboard Jam Kerja Relawan Komunitas</h1>
        <p class="text-xs text-gray-500 mt-1">Akumulasi Kontribusi Jam Sosial Orang Tua & Komunitas Sekolah</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Jam Terverifikasi</span>
            <p class="text-3xl font-black text-emerald-600 mt-1">128 Jam</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Relawan Aktif</span>
            <p class="text-3xl font-black text-indigo-600 mt-1">45 Orang</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Kegiatan Selesai</span>
            <p class="text-3xl font-black text-sky-600 mt-1">12 Acara</p>
        </div>
    </div>
</div>
@endsection
