@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Selisih Rekonsiliasi</h1>
        <form method="GET" action="{{ route('treasury.reconciliation.discrepancies') }}" class="flex items-center gap-2">
            <select name="bank_account_id" class="rounded-md border-gray-300 shadow-sm">
                <option value="">Semua rekening</option>
                @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" @selected((string) $accountId === (string) $acc->id)>{{ $acc->bank_name }} - {{ $acc->account_number }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Filter</button>
        </form>
    </div>
    @include('treasury.partials.alerts')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Mutasi masuk belum cocok</p>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($totals['credit'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Mutasi keluar belum cocok</p>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($totals['debit'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Selisih nominal (disputed)</p>
            <div class="mt-2 text-2xl font-black text-amber-700">{{ $totals['disputed'] }} mutasi</div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rekening</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Debit</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Kredit</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($transactions as $tx)
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        {{ $tx->statement->account->bank_name }} - {{ $tx->statement->account->account_number }}
                        <div><a href="{{ route('treasury.reconciliation.review', $tx->bank_statement_id) }}" class="text-xs text-indigo-600 font-bold">Rekening koran #{{ $tx->bank_statement_id }}</a></div>
                    </td>
                    <td class="px-6 py-4">
                        {{ $tx->description }}
                        @if($tx->reference_number)<div class="text-xs text-gray-400">Ref: {{ $tx->reference_number }}</div>@endif
                    </td>
                    <td class="px-6 py-4 text-right">{{ $tx->debit > 0 ? 'Rp '.number_format($tx->debit, 0, ',', '.') : '-' }}</td>
                    <td class="px-6 py-4 text-right">{{ $tx->credit > 0 ? 'Rp '.number_format($tx->credit, 0, ',', '.') : '-' }}</td>
                    <td class="px-6 py-4">
                        @if($tx->match_status === 'disputed')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Selisih</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Belum Cocok</span>
                        @endif
                        <div class="mt-1">{{ $tx->match_notes }}</div>
                        @if($tx->credit > 0)
                        <form method="POST" action="{{ route('treasury.reconciliation.manualMatch') }}" class="mt-2 flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $tx->id }}">
                            <input type="number" name="billing_id" min="1" value="{{ $tx->matched_billing_id }}" placeholder="ID tagihan" class="w-28 rounded-md border-gray-300 shadow-sm text-xs">
                            <button type="submit" class="text-indigo-600 font-bold text-xs hover:text-indigo-900">Cocokkan</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">Tidak ada selisih: semua mutasi sudah cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
