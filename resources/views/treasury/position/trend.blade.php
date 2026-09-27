@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Tren Likuiditas</h1>
    @forelse($positions as $accountPositions)
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm mb-6">
        <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">{{ $accountPositions->first()->account->bank_name }} - {{ $accountPositions->first()->account->account_name }}</h3>
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Saldo Awal</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Masuk</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Keluar</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Saldo Akhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($accountPositions as $pos)
                <tr>
                    <td class="px-6 py-3">{{ $pos->position_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-right">Rp {{ number_format($pos->opening_balance, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-right">Rp {{ number_format($pos->total_inflow, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-right">Rp {{ number_format($pos->total_outflow, 0, ',', '.') }}</td>
                    <td class="px-6 py-3 text-right font-bold">Rp {{ number_format($pos->closing_balance, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Belum ada posisi kas harian dalam 30 hari terakhir. Posisi dihitung saat halaman Posisi Keuangan dibuka untuk suatu tanggal.</p>
    </div>
    @endforelse
</div>
@endsection
