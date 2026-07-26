@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                RAPBS Multi-Year Budget Planning & Variance
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perencanaan Rencana Anggaran Pendapatan & Belanja Sekolah (SMA Islam Sultan Agung 1 Semarang)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('financial.budget.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md flex items-center gap-2">
                + Buat RAPBS Baru
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Cards Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Pendapatan Direncanakan</span>
            <p class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($activePlan->total_revenue_planned ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Belanja Direncanakan</span>
            <p class="text-xl font-black text-red-500 mt-1">Rp {{ number_format($activePlan->total_expense_planned ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Proyeksi Surplus / Defisit</span>
            @php $net = ($activePlan->total_revenue_planned ?? 0) - ($activePlan->total_expense_planned ?? 0); @endphp
            <p class="text-xl font-black {{ $net >= 0 ? 'text-teal-600' : 'text-rose-600' }} mt-1">
                Rp {{ number_format($net, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Status RAPBS Aktif</span>
            <p class="text-sm font-bold mt-2">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-black uppercase">
                    {{ $activePlan->status ?? 'Draft' }}
                </span>
            </p>
        </div>
    </div>

    <!-- Active RAPBS List Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Dokumen RAPBS Multi-Tahun</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="py-3 px-4">Tahun Fiskal</th>
                        <th class="py-3 px-4">Judul RAPBS</th>
                        <th class="py-3 px-4">Pendapatan</th>
                        <th class="py-3 px-4">Belanja</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($plans as $plan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-750">
                        <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $plan->fiscal_year }}</td>
                        <td class="py-3 px-4 font-semibold">{{ $plan->title }}</td>
                        <td class="py-3 px-4 font-semibold text-emerald-600">Rp {{ number_format($plan->total_revenue_planned, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 font-semibold text-red-500">Rp {{ number_format($plan->total_expense_planned, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                                {{ $plan->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($plan->status === 'submitted' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $plan->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ route('financial.budget.show', $plan->id) }}" class="text-xs font-bold text-emerald-600 hover:underline">Rincian</a>
                            <a href="{{ route('financial.budget.variance', $plan->id) }}" class="text-xs font-bold text-teal-600 hover:underline">Variansi</a>
                            <a href="{{ route('financial.budget.forecast', $plan->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">Proyeksi</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-400">Belum ada dokumen RAPBS yang dibuat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
