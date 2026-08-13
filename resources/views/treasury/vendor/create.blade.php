@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Pembayaran Vendor</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.vendor.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Vendor</label>
                <input type="text" name="vendor_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" name="amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jatuh Tempo</label>
                <input type="date" name="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Metode</label>
                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="transfer">Transfer</option>
                    <option value="cash">Cash</option>
                </select></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection