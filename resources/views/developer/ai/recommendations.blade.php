@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                AI Recommendation & Personalization Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rekomendasi Cerdas Bimbingan Belajar & Kurikulum Berbasis ML/AI</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Rekomendasi Cerdas Sistem</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Tipe Rekomendasi</th>
                    <th class="py-3 px-4">Pengguna</th>
                    <th class="py-3 px-4">Detail Saran AI</th>
                    <th class="py-3 px-4">Skor Kepercayaan</th>
                    <th class="py-3 px-4 text-right">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recommendations as $rec)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $rec->recommendation_type) }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $rec->user->name ?? '-' }}</td>
                    <td class="py-3 px-4 text-xs">{{ $rec->suggestion_text }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-purple-600">{{ number_format($rec->confidence_score * 100, 1) }}%</td>
                    <td class="py-3 px-4 text-right">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-purple-100 text-purple-700">
                            {{ strtoupper($rec->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada rekomendasi AI yang dibuat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
