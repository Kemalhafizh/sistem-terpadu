@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                School-Community Partnership Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kemitraan Strategis Universitas, Dunia Usaha & Instansi SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('partnerships.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-bold text-xs shadow-md">+ Registrasi Mitra Baru</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Mitra Komunitas & MOU Aktif</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Organisasi / Perusahaan</th>
                    <th class="py-3 px-4">Kategori Mitra</th>
                    <th class="py-3 px-4">Contact Person</th>
                    <th class="py-3 px-4">Status MOU</th>
                    <th class="py-3 px-4 text-right">Berlaku Sampai</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($partnerships as $pt)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $pt->organization_name }}</td>
                    <td class="py-3 px-4 uppercase text-xs font-semibold">{{ $pt->partner_type }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $pt->contact_person ?? '-' }} ({{ $pt->contact_email ?? '-' }})</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($pt->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right text-xs font-mono">{{ $pt->valid_until ? $pt->valid_until->format('d M Y') : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada data kemitraan aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
