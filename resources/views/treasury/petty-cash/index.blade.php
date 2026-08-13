@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Kas Kecil</h1>
        <a href="{{ route('treasury.petty-cash.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Catat Transaksi</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($funds as $fund)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $fund->fund_name }}</h3>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($fund->current_balance, 0, ',', '.') }}</div>
            <p class="text-gray-500 text-sm mt-1">Imprest: Rp {{ number_format($fund->imprest_amount, 0, ',', '.') }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection