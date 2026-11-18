@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Tambah Pembayaran Vendor</h1>
    @include('treasury.partials.alerts')
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('treasury.vendor.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nama Vendor</label>
                <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Nomor Invoice</label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jumlah (Rp)</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jatuh Tempo</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Metode</label>
                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach(\App\Models\VendorPayment::METHOD_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Rekening Sumber (opsional)</label>
                <select name="bank_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">— Tentukan saat pembayaran —</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected((string) old('bank_account_id') === (string) $acc->id)>{{ $acc->bank_name }} - {{ $acc->account_number }}</option>
                    @endforeach
                </select></div>
                <div class="md:col-span-2"><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea name="description" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea></div>
            </div>
            <div class="mt-6"><button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md font-bold">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
