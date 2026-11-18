@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Rekonsiliasi Bank</h1>
        <div class="flex gap-2">
            <a href="{{ route('treasury.reconciliation.discrepancies') }}" class="bg-white border border-indigo-600 text-indigo-600 px-4 py-2 rounded-md font-bold">Selisih</a>
            <a href="{{ route('treasury.reconciliation.import') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Impor Rekening Koran</a>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rekening</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Periode</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Transaksi</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Cocok</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Belum Cocok</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($statements as $st)
                <tr>
                    <td class="px-6 py-4">{{ $st->statement_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">{{ $st->account->bank_name }} - {{ $st->account->account_number }}</td>
                    <td class="px-6 py-4">{{ $st->period_start->format('d/m/Y') }} s.d. {{ $st->period_end->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">{{ $st->total_transactions }}</td>
                    <td class="px-6 py-4 text-right">{{ $st->matched_count }}</td>
                    <td class="px-6 py-4 text-right">{{ $st->unmatched_count }}</td>
                    <td class="px-6 py-4">{{ ['imported' => 'Diimpor', 'processing' => 'Diproses', 'reconciled' => 'Selesai', 'has_discrepancies' => 'Ada Selisih'][$st->status] ?? $st->status }}</td>
                    <td class="px-6 py-4"><a href="{{ route('treasury.reconciliation.review', $st->id) }}" class="text-indigo-600 font-bold hover:text-indigo-900">Review</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-6 text-center text-gray-500">Belum ada rekening koran yang diimpor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection