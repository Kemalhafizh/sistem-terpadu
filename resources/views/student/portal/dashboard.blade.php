@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Student Self-Service Portal
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Layanan Mandiri Pengajuan Surat, Transkrip & Dokumen Siswa SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('student.portal.create_request') }}" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-2xl font-bold text-xs shadow-md">+ Ajukan Dokumen Baru</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Riwayat Pengajuan Layanan Mandiri</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Jenis Dokumen</th>
                    <th class="py-3 px-4">Status Pengajuan</th>
                    <th class="py-3 px-4">Tanggal Pengajuan</th>
                    <th class="py-3 px-4 text-right">Catatan TU</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($requests as $req)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white uppercase">{{ str_replace('_', ' ', $req->request_type) }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $req->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : ($req->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                            {{ strtoupper($req->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $req->created_at ? $req->created_at->format('d M Y H:i') : '-' }}</td>
                    <td class="py-3 px-4 text-right text-xs text-gray-500">{{ $req->admin_notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada pengajuan dokumen mandiri.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
