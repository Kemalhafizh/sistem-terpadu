@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                Developer API Gateway & Webhook Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manajemen API Key Terenkripsi, Scope Izin & Langganan Webhook Real-time</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-black text-gray-900 dark:text-white">API Keys Aktif</h3>
            <div class="space-y-3">
                @forelse($keys as $k)
                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl space-y-1">
                    <p class="font-bold text-sm text-gray-900 dark:text-white">{{ $k->app_name }}</p>
                    <p class="text-xs font-mono text-indigo-600 truncate">{{ $k->api_key }}</p>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada API Key terdaftar.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <h3 class="text-base font-black text-gray-900 dark:text-white">Langganan Webhooks</h3>
            <div class="space-y-3">
                @forelse($webhooks as $wh)
                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl space-y-1">
                    <p class="font-bold text-xs font-mono text-gray-900 dark:text-white truncate">{{ $wh->target_url }}</p>
                    <p class="text-xs text-emerald-600 font-semibold">Events: {{ implode(', ', $wh->event_types ?? []) }}</p>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-4">Belum ada webhook aktif.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
