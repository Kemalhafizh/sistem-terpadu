@extends('layouts.app')
@section('content')
@php
    $badges = ['scheduled' => 'bg-amber-100 text-amber-800', 'overdue' => 'bg-rose-100 text-rose-800', 'paid' => 'bg-emerald-100 text-emerald-800', 'cancelled' => 'bg-gray-100 text-gray-700'];
@endphp
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Pembayaran Vendor</h1>
        <div class="flex gap-2">
            <a href="{{ route('treasury.vendor.calendar') }}" class="bg-white border border-indigo-600 text-indigo-600 px-4 py-2 rounded-md font-bold">Kalender</a>
            <a href="{{ route('treasury.vendor.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Tambah Pembayaran</a>
        </div>
    </div>
    @include('treasury.partials.alerts')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Terjadwal (belum jatuh tempo)</p>
            <div class="mt-2 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($summary['scheduled'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Terlambat (lewat jatuh tempo)</p>
            <div class="mt-2 text-2xl font-black text-rose-700">Rp {{ number_format($summary['overdue'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <p class="text-sm text-gray-500">Dibayar bulan ini</p>
            <div class="mt-2 text-2xl font-black text-emerald-700">Rp {{ number_format($summary['paid_this_month'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Vendor</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($payments as $pay)
                <tr>
                    <td class="px-6 py-4">
                        {{ $pay->vendor_name }}
                        @if($pay->invoice_number)<div class="text-xs text-gray-400">Invoice {{ $pay->invoice_number }}</div>@endif
                    </td>
                    <td class="px-6 py-4">{{ $pay->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-right">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badges[$pay->effectiveStatus()] ?? '' }}">{{ $pay->statusLabel() }}</span>
                        @if($pay->paid_date)<div class="text-xs text-gray-400 mt-1">Dibayar {{ $pay->paid_date->format('d/m/Y') }}</div>@endif
                    </td>
                    <td class="px-6 py-4"><a href="{{ route('treasury.vendor.show', $pay) }}" class="text-indigo-600 font-bold hover:text-indigo-900">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-6 text-center text-gray-500">Belum ada pembayaran vendor.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
