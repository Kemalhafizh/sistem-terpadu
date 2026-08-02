@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-xl text-center space-y-4">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Sertifikat Digital Sah & Terverifikasi</h1>
        <p class="text-xs text-gray-500">SMA Islam Sultan Agung 1 Semarang</p>

        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-2xl text-left space-y-2 border border-gray-200 dark:border-gray-700 text-sm">
            <div class="flex justify-between">
                <span class="text-xs font-bold text-gray-400">Penerima</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $cert->student->name ?? 'Siswa' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-bold text-gray-400">Kode Verifikasi</span>
                <span class="font-mono font-bold text-amber-600">{{ $cert->verification_code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-xs font-bold text-gray-400">Tanggal Terbit</span>
                <span class="font-mono text-xs">{{ $cert->issued_at ? $cert->issued_at->format('d M Y') : '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
