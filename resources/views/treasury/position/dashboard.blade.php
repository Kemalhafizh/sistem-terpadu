@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Posisi Keuangan</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($positions as $pos)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $pos->account->bank_name ?? 'Bank' }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $pos->position_date->format('d/m/Y') }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($pos->closing_balance, 0, ',', '.') }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection