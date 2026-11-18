@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                Student Achievement Certificate Generator
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Penerbitan & Verifikasi Sertifikat Digital Otomatis Ber-QR Code SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Sertifikat Terbit</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Kode Verifikasi</th>
                    <th class="py-3 px-4">Tanggal Terbit</th>
                    <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($certificates as $cert)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $cert->student->name ?? 'Siswa' }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-amber-600">{{ $cert->verification_code }}</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $cert->issued_at ? $cert->issued_at->format('d M Y') : '-' }}</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('certificates.verify', $cert->verification_code) }}" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold">Verifikasi Public &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada sertifikat digital yang diterbitkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
