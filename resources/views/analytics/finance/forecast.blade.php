@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Financial Forecasting & Cash Flow Projection
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Simulasi Arus Kas & Proyeksi Pendapatan/Pengeluaran RAPBS Jangka Panjang</p>
        </div>
        <a href="{{ route('analytics.finance.create_scenario') }}" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-2xl font-bold text-xs shadow-md">+ Buat Skenario Simulasi Baru</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Forecast List -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Skenario Proyeksi Keuangan Tersimpan</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Skenario</th>
                    <th class="py-3 px-4">Proyeksi Pendapatan</th>
                    <th class="py-3 px-4">Proyeksi Pengeluaran</th>
                    <th class="py-3 px-4">Surplus / Defisit</th>
                    <th class="py-3 px-4">Confidence Interval</th>
                    <th class="py-3 px-4 text-right">Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($forecasts as $fc)
                @php $net = $fc->projected_revenue - $fc->projected_expense; @endphp
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $fc->scenario_name }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-emerald-600">Rp {{ number_format($fc->projected_revenue, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-rose-600">Rp {{ number_format($fc->projected_expense, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 font-mono font-black {{ $net >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        Rp {{ number_format($net, 0, ',', '.') }}
                    </td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $fc->confidence_interval }}%</td>
                    <td class="py-3 px-4 text-right font-bold text-gray-700 dark:text-gray-300">{{ $fc->creator->name ?? 'Admin Finance' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada skenario proyeksi keuangan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
