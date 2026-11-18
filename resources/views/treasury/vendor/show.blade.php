@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    @include('treasury.partials.nav')
    <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">{{ school_name() }} - Detail Pembayaran Vendor</h1>
    @include('treasury.partials.alerts')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ $payment->vendor_name }}</h3>
            <div class="mt-2 text-3xl font-black text-gray-900 dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
            <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div><dt class="text-gray-500">Status</dt><dd class="font-bold text-gray-900 dark:text-white">{{ $payment->statusLabel() }}</dd></div>
                <div><dt class="text-gray-500">Jatuh tempo</dt><dd class="text-gray-900 dark:text-white">{{ $payment->due_date->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500">Nomor invoice</dt><dd class="text-gray-900 dark:text-white">{{ $payment->invoice_number ?: '-' }}</dd></div>
                <div><dt class="text-gray-500">Metode</dt><dd class="text-gray-900 dark:text-white">{{ \App\Models\VendorPayment::METHOD_LABELS[$payment->payment_method] ?? $payment->payment_method }}</dd></div>
                <div><dt class="text-gray-500">Rekening sumber</dt><dd class="text-gray-900 dark:text-white">{{ $payment->account ? $payment->account->bank_name.' - '.$payment->account->account_number : '-' }}</dd></div>
                <div><dt class="text-gray-500">Tanggal bayar</dt><dd class="text-gray-900 dark:text-white">{{ $payment->paid_date?->format('d/m/Y') ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Dicatat oleh</dt><dd class="text-gray-900 dark:text-white">{{ $payment->creator?->name ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">{{ $payment->status === 'cancelled' ? 'Dibatalkan oleh' : 'Dibayar/disetujui oleh' }}</dt><dd class="text-gray-900 dark:text-white">{{ $payment->approver?->name ?? '-' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-gray-500">Keterangan</dt><dd class="text-gray-900 dark:text-white">{{ $payment->description }}</dd></div>
                @if($payment->proof_file_path)
                <div class="sm:col-span-2"><dt class="text-gray-500">Bukti bayar</dt><dd><a href="{{ route('treasury.vendor.proof', $payment) }}" class="text-indigo-600 font-bold hover:text-indigo-900">Unduh bukti</a></dd></div>
                @endif
            </dl>
        </div>
        @if($payment->isOpen())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-4">Catat Pembayaran</h3>
            <form action="{{ route('treasury.vendor.pay', $payment) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tanggal bayar</label>
                <input type="date" name="paid_date" value="{{ old('paid_date', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Metode</label>
                <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @foreach(\App\Models\VendorPayment::METHOD_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $payment->payment_method) === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Rekening sumber</label>
                <select name="bank_account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">— Tunai / tidak lewat bank —</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected((string) old('bank_account_id', $payment->bank_account_id) === (string) $acc->id)>{{ $acc->bank_name }} - {{ $acc->account_number }}</option>
                    @endforeach
                </select></div>
                <div><label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Bukti bayar (PDF/JPG/PNG, maks 5 MB)</label>
                <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm"></div>
                <button type="submit" class="w-full bg-emerald-600 text-white px-4 py-2 rounded-md font-bold">Tandai Lunas</button>
            </form>
            <form action="{{ route('treasury.vendor.cancel', $payment) }}" method="POST" class="mt-4" onsubmit="return confirm('Batalkan pembayaran vendor ini?')">
                @csrf
                <button type="submit" class="w-full bg-white border border-rose-600 text-rose-600 px-4 py-2 rounded-md font-bold">Batalkan</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
