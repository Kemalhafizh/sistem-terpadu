@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">SMA Islam Sultan Agung 1 Semarang - Tambah Rekening Bank</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.accounts.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Bank</label>
                <input type="text" name="bank_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nomor Rekening</label>
                <input type="text" name="account_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Rekening</label>
                <input type="text" name="account_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis Rekening</label>
                <select name="account_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="giro">Giro</option>
                    <option value="tabungan">Tabungan</option>
                    <option value="virtual_account">Virtual Account</option>
                </select></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection