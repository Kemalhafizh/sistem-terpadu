@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Rekening Bank</h1>
        <a href="{{ route('treasury.accounts.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Rekening</a>
    </div>
    @include('treasury.partials.alerts')
    @if($accounts->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-gray-600 dark:text-gray-300">Belum ada rekening bank.</div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($accounts as $acc)
        <a href="{{ route('treasury.accounts.show', $acc) }}" class="block bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 hover:border-indigo-300 {{ $acc->is_active ? '' : 'opacity-60' }}">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $acc->bank_name }} - {{ $acc->account_name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $acc->account_number }}</p>
            @if($acc->latestPosition)
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($acc->latestPosition->closing_balance, 0, ',', '.') }}</div>
            <p class="text-gray-500 text-xs mt-1">Saldo akhir posisi kas {{ $acc->latestPosition->position_date->format('d/m/Y') }}</p>
            @else
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($acc->current_balance, 0, ',', '.') }}</div>
            <p class="text-gray-500 text-xs mt-1">Saldo awal yang diinput (belum ada posisi kas harian)</p>
            @endif
            <div class="mt-4 flex gap-2">
                @if($acc->is_primary)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Utama</span>
                @endif
                @unless($acc->is_active)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Nonaktif</span>
                @endunless
            </div>
        </a>
        @endforeach
    </div>
</div>
@endsection
