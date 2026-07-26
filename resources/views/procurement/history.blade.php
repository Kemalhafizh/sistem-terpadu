@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Riwayat & Audit Log Pengadaan (PO & GRN)</h1>
            <p class="text-xs text-gray-500 mt-1">Jejak audit alur Procurement $\rightarrow$ PO $\rightarrow$ Penerimaan Barang (GRN)</p>
        </div>
        <a href="{{ route('financial.procurement.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Judul Pengadaan</th>
                    <th class="py-3 px-4">Nomor PO</th>
                    <th class="py-3 px-4">Vendor</th>
                    <th class="py-3 px-4">Nilai Total</th>
                    <th class="py-3 px-4">Status PO</th>
                    <th class="py-3 px-4">Aksi Penerimaan (GRN)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($requests as $req)
                @if($req->purchaseOrder)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $req->title }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-teal-600">{{ $req->purchaseOrder->po_number }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $req->purchaseOrder->vendor_name }}</td>
                    <td class="py-3 px-4 font-bold">Rp {{ number_format($req->purchaseOrder->total_amount, 0, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $req->purchaseOrder->status === 'delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $req->purchaseOrder->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        @if($req->purchaseOrder->status !== 'delivered')
                        <form action="{{ route('financial.procurement.record_grn', $req->purchaseOrder->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="received_date" value="{{ date('Y-m-d') }}">
                            <input type="hidden" name="condition_notes" value="Barang diterima lengkap dan berfungsi normal.">
                            <input type="hidden" name="is_complete" value="1">
                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Catat GRN Received</button>
                        </form>
                        @else
                        <span class="text-xs font-bold text-emerald-600">Lengkap (GRN Complete)</span>
                        @endif
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada dokumen PO yang diterbitkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
