@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Form Pengajuan Dokumen Mandiri</h1>
            <p class="text-xs text-gray-500 mt-1">Layanan Tata Usaha Mandiri Siswa SMA ISA 1</p>
        </div>
        <a href="{{ route('student.portal.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <form action="{{ route('student.portal.store_request') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Dokumen</label>
                <select name="request_type" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                    <option value="transcript">Transkrip Nilai Akademik</option>
                    <option value="recommendation_letter">Surat Rekomendasi Beasiswa/Kuliah</option>
                    <option value="certificate">Sertifikat Prestasi/Keaktifan</option>
                    <option value="profile_update">Pembaruan Biodata Siswa</option>
                    <option value="document_copy">Salinan Legalisis Ijazah/Rapor</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Catatan Keperluan</label>
                <textarea name="notes" rows="3" placeholder="Persyaratan pendaftaran SNBP / Beasiswa..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs shadow-md">+ Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>
@endsection
