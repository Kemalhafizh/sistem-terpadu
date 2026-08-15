@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Dasbor Penjualan Tiket
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($events ?? [] as $event)
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $event->event_name }}</h3>
            <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-400 mb-4">
                <span>Tiket Terjual</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $event->tickets_count ?? 0 }} / {{ $event->max_capacity }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                @php $percentage = $event->max_capacity > 0 ? min(100, (($event->tickets_count ?? 0) / $event->max_capacity) * 100) : 0; @endphp
                <div class="bg-amber-500 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-3 text-center text-gray-500 py-8">
            Belum ada data penjualan.
        </div>
        @endforelse
    </div>
</div>
@endsection
