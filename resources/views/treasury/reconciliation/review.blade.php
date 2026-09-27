@extends('layouts.app')
@section('content')
@php
    $statusLabels = [
        'unmatched' => ['Belum Cocok', 'bg-rose-100 text-rose-800'],
        'disputed' => ['Selisih', 'bg-amber-100 text-amber-800'],
        'auto_matched' => ['Cocok (Otomatis)', 'bg-emerald-100 text-emerald-800'],
        'manual_matched' => ['Cocok (Manual)', 'bg-emerald-100 text-emerald-800'],
    ];
@endphp
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Review Rekonsiliasi</h1>
        <form method="POST" action="{{ route('treasury.reconciliation.autoReconcile', $statement->id) }}">
            @csrf
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Jalankan Rekonsiliasi Otomatis</button>
        </form>
    </div>
    @if(session('success'))
        <div class="mb-4 rounded-md bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first() }}</div>
    @endif
    <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
        {{ $statement->account->bank_name }} - {{ $statement->account->account_number }} &middot;
        Periode {{ $statement->period_start->format('d/m/Y') }} s.d. {{ $statement->period_end->format('d/m/Y') }} &middot;
        {{ $statement->total_transactions }} transaksi, {{ $statement->matched_count }} cocok, {{ $statement->unmatched_count }} belum cocok
    </p>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Debit</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Kredit</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($statement->transactions as $tx)
                @php
                    [$label, $badge] = $statusLabels[$tx->match_status] ?? [$tx->match_status, 'bg-gray-100 text-gray-800'];
                @endphp
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        {{ $tx->description }}
                        @if($tx->reference_number)
                            <div class="text-xs text-gray-400">Ref: {{ $tx->reference_number }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">{{ $tx->debit > 0 ? 'Rp '.number_format($tx->debit, 0, ',', '.') : '-' }}</td>
                    <td class="px-6 py-4 text-right">{{ $tx->credit > 0 ? 'Rp '.number_format($tx->credit, 0, ',', '.') : '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                        @if($tx->matched_billing_id)
                            <div class="text-xs text-gray-400 mt-1">Tagihan #{{ $tx->matched_billing_id }}{{ $tx->matchedPayment ? ' · '.$tx->matchedPayment->receipt_number : '' }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        {{ $tx->match_notes }}
                        @if(in_array($tx->match_status, ['unmatched', 'disputed']) && $tx->credit > 0)
                        <form method="POST" action="{{ route('treasury.reconciliation.manualMatch') }}" class="mt-2 flex items-center gap-2">
                            @csrf
                            <input type="hidden" name="transaction_id" value="{{ $tx->id }}">
                            <input type="number" name="billing_id" min="1" placeholder="ID tagihan" class="w-28 rounded-md border-gray-300 shadow-sm text-xs">
                            <button type="submit" class="text-indigo-600 font-bold text-xs hover:text-indigo-900">Cocokkan</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
