@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Kelengkapan Terjemahan Multi-Bahasa</h1>
            <p class="text-xs text-gray-500 mt-1">Metrik persentase cakupan i18n platform SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('ux.i18n.manager') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- Progress Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Bahasa Indonesia (ID)</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $completion['id'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">English (EN)</span>
            <p class="text-2xl font-black text-indigo-600 mt-1">{{ $completion['en'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">العربية - Arab (AR RTL)</span>
            <p class="text-2xl font-black text-teal-600 mt-1">{{ $completion['ar'] }}%</p>
        </div>
    </div>
</div>
@endsection
