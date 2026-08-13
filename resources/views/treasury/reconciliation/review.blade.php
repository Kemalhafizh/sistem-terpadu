@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Review Rekonsiliasi</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Debit/Kredit</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($statement->transactions as $tx)
                <tr>
                    <td class="px-6 py-4">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">{{ $tx->description }}</td>
                    <td class="px-6 py-4">D: {{ $tx->debit }} / K: {{ $tx->credit }}</td>
                    <td class="px-6 py-4">
                        @if($tx->match_status == 'unmatched')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Belum Cocok</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Cocok</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection