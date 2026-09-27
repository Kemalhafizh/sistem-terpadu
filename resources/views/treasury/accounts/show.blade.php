@extends('layouts.app')
@section('content')
@php
    $statementStatus = ['imported' => 'Diimpor', 'processing' => 'Diproses', 'reconciled' => 'Selesai', 'has_discrepancies' => 'Ada Selisih'];
@endphp
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - {{ $account->bank_name }} {{ $account->account_number }}</h1>
        <a href="{{ route('treasury.accounts.edit', $account) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Ubah</a>
    </div>
    @include('treasury.partials.alerts')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">{{ $account->account_name }} &middot; {{ ['giro' => 'Giro', 'tabungan' => 'Tabungan', 'virtual_account' => 'Virtual Account'][$account->account_type] ?? $account->account_type }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $account->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700' }}">{{ $account->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                @if($account->is_primary)<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Utama</span>@endif
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Saldo awal yang diinput</p>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            @if($account->latestPosition)
                <p class="text-sm text-gray-500">Saldo akhir posisi kas {{ $account->latestPosition->position_date->format('d/m/Y') }}</p>
                <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($account->latestPosition->closing_balance, 0, ',', '.') }}</div>
            @else
                <p class="text-sm text-gray-500">Posisi kas harian</p>
                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">Belum dihitung. Buka <a href="{{ route('treasury.position.dashboard') }}" class="text-indigo-600 font-bold">Posisi Kas</a>.</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Rekening Koran Terakhir</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($statements as $st)
                    <tr>
                        <td class="px-6 py-3">{{ $st->period_start->format('d/m/Y') }} s.d. {{ $st->period_end->format('d/m/Y') }}</td>
                        <td class="px-6 py-3">{{ $st->matched_count }}/{{ $st->total_transactions }} cocok</td>
                        <td class="px-6 py-3">{{ $statementStatus[$st->status] ?? $st->status }}</td>
                        <td class="px-6 py-3"><a href="{{ route('treasury.reconciliation.review', $st->id) }}" class="text-indigo-600 font-bold">Review</a></td>
                    </tr>
                    @empty
                    <tr><td class="px-6 py-4 text-gray-500">Belum ada rekening koran yang diimpor.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Posisi Kas Harian</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($positions as $pos)
                    <tr>
                        <td class="px-6 py-3">{{ $pos->position_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-right text-emerald-700">+Rp {{ number_format($pos->total_inflow, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right text-rose-700">-Rp {{ number_format($pos->total_outflow, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-bold">Rp {{ number_format($pos->closing_balance, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td class="px-6 py-4 text-gray-500">Belum ada posisi kas harian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm lg:col-span-2">
            <h3 class="px-6 pt-6 font-bold text-lg text-gray-900 dark:text-white">Pembayaran Vendor dari Rekening Ini</h3>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mt-4 text-sm">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                    @forelse($vendorPayments as $pay)
                    <tr>
                        <td class="px-6 py-3"><a href="{{ route('treasury.vendor.show', $pay) }}" class="text-indigo-600 font-bold">{{ $pay->vendor_name }}</a></td>
                        <td class="px-6 py-3">Jatuh tempo {{ $pay->due_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 text-right">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-3">{{ $pay->statusLabel() }}</td>
                    </tr>
                    @empty
                    <tr><td class="px-6 py-4 text-gray-500">Belum ada pembayaran vendor dari rekening ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
