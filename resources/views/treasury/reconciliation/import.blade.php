@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Impor Rekening Koran</h1>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        @if($errors->any())
            <div class="mb-4 rounded-md bg-rose-50 p-4 text-sm text-rose-800">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('treasury.reconciliation.processImport') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Pilih Rekening</label>
                <select name="bank_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->bank_name }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">File CSV</label>
                <input type="file" name="file" accept=".csv,text/csv" class="mt-1 block w-full">
                <p class="text-xs text-gray-500 mt-1">Header: tanggal, keterangan, referensi, debit, kredit, saldo (pemisah koma atau titik koma).</p></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Impor Data</button></div>
        </form>
    </div>
</div>
@endsection