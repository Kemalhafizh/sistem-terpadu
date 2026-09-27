@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Tambah Kas Kecil</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        @if($funds->isEmpty())
            <p class="text-gray-600 dark:text-gray-300">Belum ada dana kas kecil yang aktif. <a href="{{ route('treasury.petty-cash.funds.create') }}" class="text-indigo-600 font-bold">Buat dana</a> terlebih dahulu.</p>
        @else
        <form action="{{ route('treasury.petty-cash.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Dana</label>
                <select name="fund_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($funds as $fund)
                        <option value="{{ $fund->id }}" @selected((string) old('fund_id', request('fund_id')) === (string) $fund->id)>{{ $fund->fund_name }} (saldo Rp {{ number_format($fund->current_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach(\App\Models\PettyCashTransaction::TYPE_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Kategori</label>
                <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach(\App\Models\PettyCashTransaction::CATEGORY_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tanggal</label>
                <input type="date" name="transaction_date" value="{{ old('transaction_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nota / Kuitansi (opsional)</label>
                <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm"></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea></div>
            </div>
            <p class="mt-4 text-xs text-gray-500">Transaksi menunggu persetujuan pejabat lain; saldo dana baru berubah setelah disetujui.</p>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
        @endif
    </div>
</div>
@endsection
