@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Parent Engagement Analytics Dashboard
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitoring Frekuensi Login & Partisipasi Orang Tua Siswa SMA ISA 1 Semarang</p>
        </div>
    </div>

    <!-- Engagement Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Metrik Skor Partisipasi Orang Tua</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Orang Tua</th>
                    <th class="py-3 px-4">Jumlah Login Portal</th>
                    <th class="py-3 px-4">Balasan Pesan</th>
                    <th class="py-3 px-4">Kehadiran Acara PTC</th>
                    <th class="py-3 px-4">Engagement Score</th>
                    <th class="py-3 px-4 text-right">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($metrics as $met)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $met->parent->name ?? 'Wali Murid' }}</td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $met->login_count }} Kali</td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $met->message_replies }} Pesan</td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $met->event_attendance }} Event</td>
                    <td class="py-3 px-4 font-black text-emerald-600">{{ $met->engagement_score }} Poin</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('analytics.engagement.detail', $met->id) }}" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">Rincian</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada metrik partisipasi orang tua.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
