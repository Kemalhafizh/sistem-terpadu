@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Import Dataset Rata-Rata Nasional</h1>
            <p class="text-xs text-gray-500 mt-1">Upload Data Agregat Kompetensi Kemdikbud/Kemenag</p>
        </div>
        <a href="{{ route('analytics.benchmarking.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm text-center py-10 space-y-4">
        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Pilih file CSV / Excel statistik rata-rata nilai nasional per mata pelajaran:</p>
        <input type="file" class="block w-full text-sm text-gray-500 border border-gray-300 rounded-xl p-2 bg-gray-50">
        <button type="button" class="px-6 py-2.5 bg-purple-600 text-white rounded-xl font-bold text-xs shadow-md">Proses Import Data</button>
    </div>
</div>
@endsection
