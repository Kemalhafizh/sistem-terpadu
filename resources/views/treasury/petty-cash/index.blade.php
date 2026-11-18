@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Kas Kecil</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('treasury.petty-cash.approvals') }}" class="bg-white border border-indigo-600 text-indigo-600 px-4 py-2 rounded-md font-bold">Persetujuan</a>
            <a href="{{ route('treasury.petty-cash.report') }}" class="bg-white border border-indigo-600 text-indigo-600 px-4 py-2 rounded-md font-bold">Laporan</a>
            <a href="{{ route('treasury.petty-cash.funds.create') }}" class="bg-white border border-indigo-600 text-indigo-600 px-4 py-2 rounded-md font-bold">Buat Dana</a>
            <a href="{{ route('treasury.petty-cash.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Catat Transaksi</a>
        </div>
    </div>
    @include('treasury.partials.alerts')
    @php($unappliedTotal = $funds->sum('unapplied_count'))
    @if($unappliedTotal > 0)
        <div class="mb-6 p-4 rounded-2xl border border-amber-300 bg-amber-50 text-amber-800 text-sm">
            <p class="font-bold">Saldo belum memperhitungkan {{ $unappliedTotal }} transaksi lama yang sudah disetujui.</p>
            <p class="mt-1">Transaksi itu disetujui sebelum persetujuan ikut mengubah saldo, sehingga saldo di bawah belum memperhitungkannya. Admin perlu menjalankan <code>php artisan treasury:apply-legacy-petty-cash</code> (tinjau dulu), lalu <code>--apply</code>.</p>
        </div>
    @endif
    @if($funds->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-gray-600 dark:text-gray-300">Belum ada dana kas kecil.</div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($funds as $fund)
        @php($usage = $fund->imprest_amount > 0 ? min(100, round($fund->current_balance / $fund->imprest_amount * 100)) : 0)
        <a href="{{ route('treasury.petty-cash.show', $fund->id) }}" class="block bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 hover:border-indigo-300 {{ $fund->is_active ? '' : 'opacity-60' }}">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $fund->fund_name }}</h3>
            <p class="text-gray-500 text-sm">Pemegang: {{ $fund->custodian?->name ?? '-' }}</p>
            <div class="mt-4 text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($fund->current_balance, 0, ',', '.') }}</div>
            <p class="text-gray-500 text-sm mt-1">Imprest: Rp {{ number_format($fund->imprest_amount, 0, ',', '.') }} &middot; tersisa {{ $usage }}%</p>
            <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-700"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ $usage }}%"></div></div>
            @if($fund->unapplied_count)
                <p class="mt-3 text-xs font-bold text-amber-700">Saldo belum termasuk {{ $fund->unapplied_count }} transaksi lama yang disetujui</p>
            @endif
            @if($fund->pending_count)
                <p class="mt-3 text-xs font-bold text-amber-700">{{ $fund->pending_count }} transaksi menunggu persetujuan</p>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endsection
