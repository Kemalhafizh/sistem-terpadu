@extends('layouts.app')
@section('content')
@php
    $badges = ['pending' => 'bg-amber-100 text-amber-800', 'approved' => 'bg-emerald-100 text-emerald-800', 'rejected' => 'bg-rose-100 text-rose-800'];
@endphp
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - {{ $fund->fund_name }}</h1>
        @if($fund->is_active)
            <a href="{{ route('treasury.petty-cash.create', ['fund_id' => $fund->id]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Catat Transaksi</a>
        @endif
    </div>
    @include('treasury.partials.alerts')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Saldo saat ini</p>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($fund->current_balance, 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 mt-1">Imprest Rp {{ number_format($fund->imprest_amount, 0, ',', '.') }} &middot; pemegang {{ $fund->custodian?->name ?? '-' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Total pengeluaran disetujui</p>
            <div class="mt-2 text-2xl font-black text-rose-700">Rp {{ number_format($totals['spent'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Total pengisian ulang disetujui</p>
            <div class="mt-2 text-2xl font-black text-emerald-700">Rp {{ number_format($totals['replenished'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Pengeluaran menunggu persetujuan</p>
            <div class="mt-2 text-2xl font-black text-amber-700">Rp {{ number_format($totals['pending'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($transactions as $tx)
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        {{ $tx->description }}
                        <div class="text-xs text-gray-400">Diajukan {{ $tx->requester?->name ?? '-' }}@if($tx->approver) &middot; diputuskan {{ $tx->approver->name }}@endif</div>
                        @if($tx->rejection_reason)<div class="text-xs text-rose-600">Alasan: {{ $tx->rejection_reason }}</div>@endif
                    </td>
                    <td class="px-6 py-4">{{ \App\Models\PettyCashTransaction::CATEGORY_LABELS[$tx->category] ?? $tx->category }}</td>
                    <td class="px-6 py-4 text-right font-bold {{ $tx->type === 'pengeluaran' ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ $tx->type === 'pengeluaran' ? '-' : '+' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4"><span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badges[$tx->approval_status] ?? '' }}">{{ \App\Models\PettyCashTransaction::STATUS_LABELS[$tx->approval_status] ?? $tx->approval_status }}</span></td>
                    <td class="px-6 py-4">
                        @if($tx->approval_status === 'pending')
                            @include('treasury.petty-cash._decision', ['tx' => $tx])
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">Belum ada transaksi untuk dana ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
