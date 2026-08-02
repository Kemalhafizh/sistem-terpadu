@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Volunteer Management & Hour Tracking
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Peluang Relawan Orang Tua & Alokasi Jam Kerja Komunitas SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Peluang Kegiatan Relawan Aktif</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Kegiatan</th>
                    <th class="py-3 px-4">Target Jam Kerja</th>
                    <th class="py-3 px-4">Tanggal Acara</th>
                    <th class="py-3 px-4">Penyelenggara</th>
                    <th class="py-3 px-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($opportunities as $op)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $op->title }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-emerald-600">{{ $op->required_hours }} Jam</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $op->event_date ? $op->event_date->format('d M Y') : '-' }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $op->organizer->name ?? 'Panitia Sekolah' }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($op->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada peluang relawan yang dibuka.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
