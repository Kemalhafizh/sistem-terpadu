@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Procurement & Purchase Order (PO) Workflow
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sistem Pengadaan Barang/Jasa Terpadu End-to-End (SMA ISA 1 Semarang)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('financial.procurement.create_request') }}" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md">
                + Ajukan Pengadaan Baru
            </a>
            <a href="{{ route('financial.procurement.history') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">
                Riwayat Audit PO
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Table Procurement Requests -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Permohonan Pengadaan Barang / Jasa</h3>

        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Judul Pengadaan</th>
                    <th class="py-3 px-4">Departemen</th>
                    <th class="py-3 px-4">Urgensi</th>
                    <th class="py-3 px-4">Estimasi Budget</th>
                    <th class="py-3 px-4">Pemohon</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 text-right">Aksi Workflow</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($requests as $req)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $req->title }}</td>
                    <td class="py-3 px-4 uppercase font-semibold text-xs text-gray-500">{{ $req->department }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 text-xs font-bold rounded uppercase
                            {{ $req->urgency === 'critical' ? 'bg-red-100 text-red-700' : ($req->urgency === 'high' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $req->urgency }}
                        </span>
                    </td>
                    <td class="py-3 px-4 font-bold text-teal-600">Rp {{ number_format($req->estimated_budget, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 font-medium">{{ $req->requester->name }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'submitted' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $req->status }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1">
                        @if($req->status === 'draft')
                        <form action="{{ route('financial.procurement.submit', $req->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold">Ajukan</button>
                        </form>
                        @elseif($req->status === 'submitted')
                        <form action="{{ route('financial.procurement.approve', $req->id) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Setujui</button>
                        </form>
                        @elseif($req->status === 'approved' && !$req->purchaseOrder)
                        <a href="{{ route('financial.procurement.create_po', $req->id) }}" class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold inline-block">Terbitkan PO</a>
                        @elseif($req->purchaseOrder)
                        <span class="text-xs font-bold text-teal-600">PO: {{ $req->purchaseOrder->po_number }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-gray-400">Belum ada permohonan pengadaan barang.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
