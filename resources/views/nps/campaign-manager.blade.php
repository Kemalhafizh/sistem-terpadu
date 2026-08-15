@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Manajer Kampanye NPS - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-750">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                @forelse($campaigns ?? [] as $camp)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $camp->campaign_title }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ str_replace('_', ' ', $camp->target_audience) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $camp->start_date->format('d/m/Y') }} - {{ $camp->end_date->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-6 py-4 text-center">Tidak ada kampanye NPS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
