@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm text-center">
        <h1 class="text-xl font-black text-gray-900 dark:text-white">Web-Based QR & NFC Scanner</h1>
        <p class="text-xs text-gray-500 mt-1">Pemindaian Kartu Pelajar Digital di Gerbang Sekolah / Perpustakaan</p>
    </div>

    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm text-center space-y-4">
        <div class="w-48 h-48 mx-auto bg-gray-900 rounded-2xl border-4 border-indigo-500 flex items-center justify-center text-indigo-400 font-mono text-xs p-4">
            Camera Feed Active / Scanning QR...
        </div>
        <p class="text-xs font-semibold text-gray-500">Arahkan QR Code Kartu Pelajar Siswa ke Kamera</p>
    </div>
</div>
@endsection
