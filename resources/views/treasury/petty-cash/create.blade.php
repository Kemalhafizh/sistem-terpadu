@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Kas Kecil</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.petty-cash.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Dana</label>
                <select name="fund_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($funds as $fund)
                        <option value="{{ $fund->id }}">{{ $fund->fund_name }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="pengeluaran">Pengeluaran</option>
                    <option value="pengisian_ulang">Pengisian Ulang</option>
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Kategori</label>
                <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="atk">ATK</option>
                    <option value="konsumsi">Konsumsi</option>
                    <option value="lainnya">Lainnya</option>
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" name="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tanggal</label>
                <input type="date" name="transaction_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection