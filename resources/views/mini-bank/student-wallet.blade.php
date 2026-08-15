@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex items-center space-x-4 mb-8">
        <svg class="w-8 h-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Dompet Siswa Mini Bank - SMA Islam Sultan Agung 1 Semarang</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Saldo Anda: Rp 0</h2>
        <div class="mt-4 flex space-x-4">
            <button class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded">Setor</button>
            <button class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Transfer</button>
        </div>
    </div>
</div>
@endsection
