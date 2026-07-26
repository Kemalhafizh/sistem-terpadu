@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Buat Dokumen RAPBS Baru</h1>
        <p class="text-xs text-gray-500 mb-6">Rencana Anggaran Pendapatan dan Belanja Sekolah SMA Islam Sultan Agung 1 Semarang</p>

        <form action="{{ route('financial.budget.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Fiskal (misal: 2026/2027)</label>
                <input type="text" name="fiscal_year" value="2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Dokumen RAPBS</label>
                <input type="text" name="title" placeholder="RAPBS SMA ISA 1 Semarang TA 2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Pendapatan (Rp)</label>
                    <input type="number" name="total_revenue_planned" value="1500000000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Belanja (Rp)</label>
                    <input type="number" name="total_expense_planned" value="1450000000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.budget.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan RAPBS</button>
            </div>
        </form>
    </div>
</div>
@endsection
