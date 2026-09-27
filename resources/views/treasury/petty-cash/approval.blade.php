@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Persetujuan Kas Kecil</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dana</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keputusan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($pending as $tx)
                @php($insufficient = $tx->type === 'pengeluaran' && (float) $tx->amount > (float) $tx->fund->current_balance)
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('treasury.petty-cash.show', $tx->fund_id) }}" class="text-indigo-600 font-bold">{{ $tx->fund->fund_name }}</a>
                        <div class="text-xs text-gray-400">Saldo Rp {{ number_format($tx->fund->current_balance, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        {{ \App\Models\PettyCashTransaction::TYPE_LABELS[$tx->type] ?? $tx->type }} &middot; {{ \App\Models\PettyCashTransaction::CATEGORY_LABELS[$tx->category] ?? $tx->category }}
                        <div>{{ $tx->description }}</div>
                        <div class="text-xs text-gray-400">Diajukan {{ $tx->requester?->name ?? '-' }}</div>
                    </td>
                    <td class="px-6 py-4 text-right font-bold">
                        Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        @if($insufficient)<div class="text-xs font-normal text-rose-600">Melebihi saldo dana</div>@endif
                    </td>
                    <td class="px-6 py-4">@include('treasury.petty-cash._decision', ['tx' => $tx])</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">Tidak ada transaksi yang menunggu persetujuan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
