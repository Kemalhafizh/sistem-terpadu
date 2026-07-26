@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Riwayat Transaksi: {{ $costCenter->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Kode: <strong class="text-purple-600">{{ $costCenter->code }}</strong> | Pagu: Rp {{ number_format($costCenter->annual_budget_allocation, 0, ',', '.') }} | Terpakai: <strong class="text-red-500">Rp {{ number_format($costCenter->spent_amount, 0, ',', '.') }}</strong></p>
        </div>
        <a href="{{ route('financial.cost-centers.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Add Expense Form -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Catat Pengeluaran Langsung Cost Center</h3>
        <form action="{{ route('financial.cost-centers.store_expense', $costCenter->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nominal Pengeluaran (Rp)</label>
                <input type="number" name="amount" placeholder="15000000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Pengeluaran</label>
                <input type="text" name="description" placeholder="Pembelian ATK dan Konsumsi Rapat Kerja Kurikulum" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md">+ Catat Pengeluaran</button>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Jurnal Transaksi Biaya</h3>

        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Waktu</th>
                    <th class="py-3 px-4">Tipe Transaksi</th>
                    <th class="py-3 px-4">Deskripsi</th>
                    <th class="py-3 px-4">Petugas</th>
                    <th class="py-3 px-4 text-right">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($costCenter->transactions as $trx)
                <tr>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $trx->created_at->format('d M Y H:i') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded uppercase
                            {{ $trx->transaction_type === 'allocation' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $trx->transaction_type }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-semibold text-gray-900 dark:text-white">{{ $trx->description }}</td>
                    <td class="py-3 px-4 text-xs">{{ $trx->recorder->name }}</td>
                    <td class="py-3 px-4 text-right font-black {{ $trx->transaction_type === 'allocation' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $trx->transaction_type === 'allocation' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada transaksi di pusat biaya ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
