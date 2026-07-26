@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Registrasi Cost Center Departemen Baru</h1>
        <p class="text-xs text-gray-500 mb-6">Penetapan pusat biaya dan alokasi pagu anggaran tahunan unit</p>

        <form action="{{ route('financial.cost-centers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode Cost Center</label>
                    <input type="text" name="code" placeholder="CC-KUR-01" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Departemen / Unit</label>
                    <select name="department" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="kurikulum">Kurikulum & Akademik</option>
                        <option value="kesiswaan">Kesiswaan & OSIS</option>
                        <option value="humas">Humas & Kerjasama</option>
                        <option value="sarpras">Sarana & Prasarana</option>
                        <option value="tu">Tata Usaha & SDM</option>
                        <option value="keuangan">Keuangan & Akuntansi</option>
                        <option value="keislaman">Keislaman & Da'wah</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pusat Biaya (Cost Center Name)</label>
                <input type="text" name="name" placeholder="Pusat Biaya Operasional Kurikulum & Laboratorium" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Penanggung Jawab (Head of Cost Center)</label>
                <select name="head_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="">-- Pilih Penanggung Jawab --</option>
                    @foreach($heads as $h)
                    <option value="{{ $h->id }}">{{ $h->name }} ({{ strtoupper($h->role) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Alokasi Anggaran Tahunan (Pagu Rp)</label>
                <input type="number" name="annual_budget_allocation" value="250000000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.cost-centers.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan Cost Center</button>
            </div>
        </form>
    </div>
</div>
@endsection
