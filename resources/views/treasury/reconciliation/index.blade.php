@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Rekonsiliasi Bank</h1>
        <a href="{{ route('treasury.reconciliation.import') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Impor Rekening Koran</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rekening</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($statements as $st)
                <tr>
                    <td class="px-6 py-4">{{ $st->statement_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">{{ $st->account->bank_name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">{{ ucfirst($st->status) }}</td>
                    <td class="px-6 py-4"><a href="{{ route('treasury.reconciliation.review', $st->id) }}" class="text-indigo-600 font-bold hover:text-indigo-900">Review</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection