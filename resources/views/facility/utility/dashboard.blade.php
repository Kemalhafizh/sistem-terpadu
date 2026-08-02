@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                Utility Expense & Carbon Footprint Tracker
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pelaporan Pengeluaran Tagihan Operasional & Kalkulasi Emisi Karbon Sekolah</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-black text-gray-900 dark:text-white">Tagihan Utility Bulanan</h3>
            <div class="space-y-3">
                @forelse($bills as $b)
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                    <div>
                        <p class="font-bold text-sm text-gray-900 dark:text-white capitalize">{{ $b->utility_type }} ({{ $b->billing_period }})</p>
                        <p class="text-xs text-gray-400 font-mono">Penggunaan: {{ number_format($b->usage_kwh_m3) }} kWh/m3</p>
                    </div>
                    <span class="font-black text-emerald-600 font-mono">Rp {{ number_format($b->amount, 0, ',', '.') }}</span>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada tagihan utility tercatat.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-black text-gray-900 dark:text-white">Jejak Karbon (Carbon Footprint)</h3>
            <div class="space-y-3">
                @forelse($emissions as $em)
                <div class="flex justify-between items-center p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100">
                    <div>
                        <p class="font-bold text-sm text-emerald-950 dark:text-emerald-300">Periode: {{ $em->period }}</p>
                        <p class="text-xs text-emerald-600">Sumber: {{ ucfirst($em->source_category) }}</p>
                    </div>
                    <span class="font-black text-emerald-700 font-mono">{{ number_format($em->co2_kg, 1) }} kg CO2</span>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada kalkulasi emisi karbon.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
