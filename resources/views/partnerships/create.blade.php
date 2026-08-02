@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Registrasi Mitra Industri & PT</h1>
            <p class="text-xs text-gray-500 mt-1">Pendataan MOU Kerjasama Eksternal Sekolah</p>
        </div>
        <a href="{{ route('partnerships.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <form action="{{ route('partnerships.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Organisasi / Instansi</label>
                <input type="text" name="organization_name" required placeholder="Universitas Islam Sultan Agung (UNISSULA)" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Mitra</label>
                    <select name="partner_type" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                        <option value="university">Universitas / Perguruan Tinggi</option>
                        <option value="corporate">Dunia Usaha / Perusahaan</option>
                        <option value="government">Instansi Pemerintah</option>
                        <option value="ngo">NGO / Yayasan Sosial</option>
                        <option value="community">Komunitas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contact Person</label>
                    <input type="text" name="contact_person" placeholder="Drs. Ahmad Supriyadi" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email Kontak</label>
                <input type="email" name="contact_email" placeholder="kerjasama@unissula.ac.id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Simpan Data Kerjasama</button>
            </div>
        </form>
    </div>
</div>
@endsection
