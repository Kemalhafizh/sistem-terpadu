@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Rekening Bank</h1>
        <a href="{{ route('treasury.accounts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Rekening</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($accounts as $acc)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $acc->bank_name }} - {{ $acc->account_name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $acc->account_number }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</div>
            @if($acc->is_primary)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 mt-4">Utama</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection