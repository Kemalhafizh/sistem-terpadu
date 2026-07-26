@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-lg text-center space-y-6">
        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-950 text-blue-600 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>

        <div>
            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 uppercase">Presensi Kehadiran Tercatat!</span>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white mt-2">{{ $meeting->title }}</h1>
            <p class="text-xs text-gray-500 mt-1">Host: {{ $meeting->host->name }} | Provider: {{ strtoupper($meeting->provider) }}</p>
        </div>

        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 text-left text-xs space-y-2">
            <p class="font-bold text-gray-700 dark:text-gray-300">Detail Rapat:</p>
            <p class="text-gray-500">Meeting ID: <code class="font-mono text-blue-600 font-bold">{{ $meeting->meeting_id }}</code></p>
            <p class="text-gray-500">Status Presensi: <span class="font-bold text-emerald-600">Hadir Online (Auto-Recorded)</span></p>
        </div>

        <div class="flex flex-col gap-3">
            <a href="{{ $meeting->meeting_url }}" target="_blank" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-extrabold text-sm shadow-lg flex items-center justify-center gap-2">
                <span>Lanjut ke Room Video Meeting</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
            <a href="{{ route('academic.virtual.index') }}" class="text-xs font-bold text-gray-400 hover:underline">Kembali ke Daftar Rapat</a>
        </div>
    </div>
</div>
@endsection
