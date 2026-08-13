@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">SMA Islam Sultan Agung 1 Semarang - Pembayaran Vendor</h1>
        <a href="{{ route('treasury.vendor.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Pembayaran</a>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @foreach($payments as $pay)
                <tr>
                    <td class="px-6 py-4">{{ $pay->vendor_name }}</td>
                    <td class="px-6 py-4">{{ $pay->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        @if($pay->status == 'overdue')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Terlambat</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">{{ ucfirst($pay->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection