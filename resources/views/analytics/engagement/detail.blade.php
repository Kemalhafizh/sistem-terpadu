@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Rincian Engagement Rate Orang Tua</h1>
            <p class="text-xs text-gray-500 mt-1">Nama Orang Tua: <strong>{{ $metric->parent->name ?? 'Wali Murid' }}</strong></p>
        </div>
        <a href="{{ route('analytics.engagement.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
            <span class="text-xs font-bold text-gray-400 uppercase">Engagement Score</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $metric->engagement_score }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
            <span class="text-xs font-bold text-gray-400 uppercase">Jumlah Login</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metric->login_count }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
            <span class="text-xs font-bold text-gray-400 uppercase">Balasan Pesan</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metric->message_replies }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
            <span class="text-xs font-bold text-gray-400 uppercase">Kehadiran Acara</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $metric->event_attendance }}</p>
        </div>
    </div>
</div>
@endsection
