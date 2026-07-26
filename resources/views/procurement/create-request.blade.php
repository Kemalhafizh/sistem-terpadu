@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Ajukan Permohonan Pengadaan Barang / Jasa</h1>
        <p class="text-xs text-gray-500 mb-6">Formulir pengajuan pengadaan fasilitas dan operasional SMA ISA 1 Semarang</p>

        <form action="{{ route('financial.procurement.store_request') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Pengadaan</label>
                <input type="text" name="title" placeholder="misal: Pengadaan 20 Unit PC Lab Komputer" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Departemen / Unit</label>
                    <select name="department" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="it">IT & Lab Komputer</option>
                        <option value="academic">Kurikulum & Akademik</option>
                        <option value="facilities">Sarana & Prasarana</option>
                        <option value="administrative">Tata Usaha & Administrasi</option>
                        <option value="islamic">Keislaman & Lab Agama</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tingkat Urgensi</label>
                    <select name="urgency" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="normal">Normal</option>
                        <option value="low">Rendah (Low)</option>
                        <option value="high">Tinggi (High)</option>
                        <option value="critical">Sangat Mendesak (Critical)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estimasi Total Budget (Rp)</label>
                <input type="number" name="estimated_budget" placeholder="150000000" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Justifikasi Kebutuhan</label>
                <textarea name="description" rows="4" placeholder="Jelaskan kebutuhan barang/jasa dan alasan pengadaan..." required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-4 text-sm font-semibold text-gray-900 dark:text-white"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.procurement.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md">Kirim Permohonan</button>
            </div>
        </form>
    </div>
</div>
@endsection
