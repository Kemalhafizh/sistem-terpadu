@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Terbitkan Purchase Order (PO)</h1>
        <p class="text-xs text-gray-500 mb-6">Permohonan: <strong class="text-teal-600">{{ $procurementRequest->title }}</strong> (Budget: Rp {{ number_format($procurementRequest->estimated_budget, 0, ',', '.') }})</p>

        <form action="{{ route('financial.procurement.store_po', $procurementRequest->id) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Vendor / Vendor Company</label>
                    <input type="text" name="vendor_name" placeholder="PT Teknologi Utama Semarang" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kontak Vendor</label>
                    <input type="text" name="vendor_contact" placeholder="024-888999 / sales@vendor.com" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estimasi Tanggal Pengiriman (Delivery Date)</label>
                <input type="date" name="delivery_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <!-- Items -->
            <div class="border-t pt-4 border-gray-100 dark:border-gray-700 space-y-3">
                <h4 class="text-xs font-black text-gray-900 dark:text-white uppercase">Daftar Item Barang / Jasa Dalam PO</h4>
                <div class="grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-6">
                        <input type="text" name="items[0][name]" value="{{ $procurementRequest->title }}" placeholder="Nama Item" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="items[0][qty]" value="1" min="1" placeholder="Qty" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    </div>
                    <div class="col-span-4">
                        <input type="number" name="items[0][unit_price]" value="{{ $procurementRequest->estimated_budget }}" placeholder="Harga Satuan (Rp)" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-xs font-semibold">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.procurement.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md">Terbitkan PO Resmi</button>
            </div>
        </form>
    </div>
</div>
@endsection
