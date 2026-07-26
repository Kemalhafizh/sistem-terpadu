@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Verifikasi & Approval Kredit PKB/CPD Tim Guru</h1>
            <p class="text-xs text-gray-500 mt-1">Daftar pengajuan aktivitas pengembangan profesi guru yang memerlukan verifikasi manajerial</p>
        </div>
        <a href="{{ route('hc.cpd.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Guru</th>
                    <th class="py-3 px-4">Judul Kegiatan</th>
                    <th class="py-3 px-4">Kategori</th>
                    <th class="py-3 px-4">Kredit</th>
                    <th class="py-3 px-4 text-right">Aksi Verifikasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($activities as $act)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $act->employee->name }}</td>
                    <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200">{{ $act->title }}</td>
                    <td class="py-3 px-4 text-xs">{{ $act->category->name }}</td>
                    <td class="py-3 px-4 font-black text-emerald-600">+{{ $act->credits_earned }} Kredit</td>
                    <td class="py-3 px-4 text-right">
                        <form action="{{ route('hc.cpd.verify', $act->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold">Setujui & Verifikasi &rarr;</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Tidak ada pengajuan kredit PKB/CPD yang menunggu verifikasi saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
