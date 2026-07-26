@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h9m-9 0V9a2 2 0 012-2h2a2 2 0 012 2v12m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Cost Center Accounting & Departmental Budget Allocation
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pencatatan & Pelacakan Efisiensi Anggaran Berbasis Pusat Biaya Departemen (SMA ISA 1 Semarang)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('financial.cost-centers.allocate_form') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md">
                + Tambah / Alokasi Cost Center
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Cost Center Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($utilizationData as $util)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-xs font-mono font-bold text-purple-600 uppercase">{{ $util['cost_center_code'] }}</span>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mt-0.5">{{ $util['cost_center_name'] }}</h3>
                    <span class="text-xs font-semibold uppercase text-gray-400">Departemen: {{ $util['department'] }}</span>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase
                    {{ $util['status'] === 'warning' ? 'bg-amber-100 text-amber-700' : ($util['status'] === 'exceeded' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700') }}">
                    {{ $util['utilization_rate'] }}% Terserap
                </span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full {{ $util['utilization_rate'] > 90 ? 'bg-amber-500' : ($util['utilization_rate'] > 100 ? 'bg-red-500' : 'bg-purple-600') }}"
                     style="width: {{ min(100, $util['utilization_rate']) }}%"></div>
            </div>

            <div class="grid grid-cols-3 gap-2 text-xs border-t pt-3 border-gray-100 dark:border-gray-700">
                <div>
                    <span class="text-gray-400 font-semibold block">Total Alokasi</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($util['allocation'], 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-semibold block">Pengeluaran</span>
                    <span class="font-bold text-red-500">Rp {{ number_format($util['spent'], 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 font-semibold block">Sisa Anggaran</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($util['remaining'], 0, ',', '.') }}</span>
                </div>
            </div>

            @php $ccModel = $costCenters->firstWhere('code', $util['cost_center_code']); @endphp
            @if($ccModel)
            <div class="pt-2 flex justify-between items-center">
                <a href="{{ route('financial.cost-centers.transactions', $ccModel->id) }}" class="text-xs font-bold text-purple-600 hover:underline">Lihat Riwayat Transaksi &rarr;</a>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada Cost Center yang terdaftar.</div>
        @endforelse
    </div>
</div>
@endsection
