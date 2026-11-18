@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Ubah Rekening Bank</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.accounts.update', $account) }}" method="POST">
            @method('PUT')
            @include('treasury.accounts._form')
        </form>
    </div>
    <div class="mt-6 bg-white dark:bg-gray-800 rounded-3xl border border-rose-200 dark:border-rose-900 shadow-sm p-6">
        <h3 class="font-bold text-rose-700">Hapus rekening</h3>
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Hanya rekening tanpa rekening koran, posisi kas, atau pembayaran vendor yang dapat dihapus. Rekening yang sudah dipakai cukup dinonaktifkan.</p>
        <form action="{{ route('treasury.accounts.destroy', $account) }}" method="POST" class="mt-4" onsubmit="return confirm('Hapus rekening ini secara permanen?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-white border border-rose-600 text-rose-600 px-4 py-2 rounded-md font-bold">Hapus Rekening</button>
        </form>
    </div>
</div>
@endsection
