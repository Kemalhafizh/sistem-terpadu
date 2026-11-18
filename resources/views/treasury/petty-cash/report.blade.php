@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Laporan Kas Kecil</h1>
        <form method="GET" action="{{ route('treasury.petty-cash.report') }}" class="flex flex-wrap items-center gap-2">
            <input type="month" name="month" value="{{ $month->format('Y-m') }}" class="rounded-md border-gray-300 shadow-sm">
            <select name="fund_id" class="rounded-md border-gray-300 shadow-sm">
                <option value="">Semua dana</option>
                @foreach($funds as $fund)
                    <option value="{{ $fund->id }}" @selected((string) ($filters['fund_id'] ?? '') === (string) $fund->id)>{{ $fund->fund_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tampilkan</button>
        </form>
    </div>
    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Transaksi yang sudah disetujui pada {{ $month->locale('id')->translatedFormat('F Y') }}.</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Total pengeluaran</p>
            <div class="mt-2 text-2xl font-black text-rose-700">Rp {{ number_format($totals['spent'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 mt-1">{{ $spending->count() }} transaksi</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Total pengisian ulang</p>
            <div class="mt-2 text-2xl font-black text-emerald-700">Rp {{ number_format($totals['replenished'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Pengeluaran per Kategori</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-2 text-left text-xs font-bold text-gray-500 uppercase">Kategori</th>
                        <th class="px-6 py-2 text-right text-xs font-bold text-gray-500 uppercase">Transaksi</th>
                        <th class="px-6 py-2 text-right text-xs font-bold text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-2 text-right text-xs font-bold text-gray-500 uppercase">Porsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($byCategory as $row)
                    <tr>
                        <td class="px-6 py-3">{{ $row['label'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $row['count'] }}</td>
                        <td class="px-6 py-3 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right">{{ number_format($row['total'] / $totals['spent'] * 100, 1, ',', '.') }}%</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-4 text-gray-500">Tidak ada pengeluaran yang disetujui pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Pengeluaran per Dana</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($byFund as $row)
                    <tr>
                        <td class="px-6 py-3">{{ $row['fund'] }}</td>
                        <td class="px-6 py-3 text-right">{{ $row['count'] }} transaksi</td>
                        <td class="px-6 py-3 text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td class="px-6 py-4 text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm lg:col-span-2 overflow-x-auto">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Rincian Pengeluaran</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($spending as $tx)
                    <tr>
                        <td class="px-6 py-3">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3">{{ $tx->fund->fund_name }}</td>
                        <td class="px-6 py-3">{{ \App\Models\PettyCashTransaction::CATEGORY_LABELS[$tx->category] ?? $tx->category }}</td>
                        <td class="px-6 py-3">{{ $tx->description }}</td>
                        <td class="px-6 py-3 text-right">Rp {{ number_format($tx->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td class="px-6 py-4 text-gray-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
