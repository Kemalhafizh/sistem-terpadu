@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-8 text-center">
        <svg class="w-16 h-16 text-amber-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2">Pemindai Tiket Acara</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Pindai kode QR tiket di sini</p>
        
        <form action="{{ route('events.scan_ticket') }}" method="POST" class="max-w-md mx-auto">
            @csrf
            <div class="mb-4">
                <input type="text" name="ticket_code" placeholder="Masukkan Kode Tiket (mis: TKT-12345)" required autofocus class="w-full text-center text-lg py-4 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-amber-500 hover:bg-amber-600">
                Verifikasi Tiket
            </button>
        </form>

        @if(session('success'))
            <div class="mt-6 p-4 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mt-6 p-4 bg-rose-50 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-xl">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>
@endsection
