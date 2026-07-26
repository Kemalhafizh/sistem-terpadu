@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Dokumentasi Kit Komponen Blade SISTA</h1>
            <p class="text-xs text-gray-500 mt-1">Panduan Penggunaan Komponen UI Enterprise</p>
        </div>
        <a href="{{ route('ux.design_system.showcase') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Galeri Komponen</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Komponen Kit UI Utama:</h3>
        <div class="space-y-3 text-xs text-gray-700 dark:text-gray-300 font-medium">
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                <code class="font-mono text-purple-600 font-bold">&lt;x-stat-card title="..." value="..." /&gt;</code>
                <p class="mt-1 text-gray-500">Komponen menampilkan angka statistik metrik ringkasan dashboard.</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                <code class="font-mono text-purple-600 font-bold">&lt;x-status-badge type="active|pending|critical" /&gt;</code>
                <p class="mt-1 text-gray-500">Komponen badge indikator status seragam di seluruh tabel.</p>
            </div>
        </div>
    </div>
</div>
@endsection
