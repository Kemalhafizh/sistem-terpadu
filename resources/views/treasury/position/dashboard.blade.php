@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Posisi Keuangan</h1>
        <form method="GET" action="{{ route('treasury.position.dashboard') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $date }}" class="rounded-md border-gray-300 shadow-sm">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tampilkan</button>
        </form>
    </div>
    <p class="text-gray-500 text-sm mb-4">Dihitung dari mutasi rekening koran yang sudah diimpor per {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
    @if($positions->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-gray-600 dark:text-gray-300">Belum ada rekening bank aktif.</div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($positions as $pos)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $pos->account->bank_name }} - {{ $pos->account->account_name }}</h3>
            <p class="text-gray-500 text-sm mt-1">{{ $pos->position_date->format('d/m/Y') }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($pos->closing_balance, 0, ',', '.') }}</div>
            <dl class="mt-4 grid grid-cols-2 gap-y-1 text-sm text-gray-600 dark:text-gray-300">
                <dt>Saldo awal</dt><dd class="text-right">Rp {{ number_format($pos->opening_balance, 0, ',', '.') }}</dd>
                <dt>Masuk</dt><dd class="text-right text-emerald-700">Rp {{ number_format($pos->total_inflow, 0, ',', '.') }}</dd>
                <dt>Keluar</dt><dd class="text-right text-rose-700">Rp {{ number_format($pos->total_outflow, 0, ',', '.') }}</dd>
            </dl>
        </div>
        @endforeach
    </div>
</div>
@endsection
