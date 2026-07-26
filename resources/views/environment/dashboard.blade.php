@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Production Environment & Encrypted Secret Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sentralisasi Pengelolaan Kunci Rahasia Environment (Dev, Staging, Production) SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('deploy.environment.configure_form') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">
                + Tambah Environment Secret
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- System Health Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Database Connection</span>
            <p class="text-xl font-black text-emerald-600 mt-1 uppercase">{{ $health['database_connection'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Redis Cache System</span>
            <p class="text-xl font-black text-emerald-600 mt-1 uppercase">{{ $health['redis_cache'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Queue Worker Supervisor</span>
            <p class="text-xl font-black text-emerald-600 mt-1 uppercase">{{ $health['queue_worker'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">SSL Certificate Status</span>
            <p class="text-xl font-black text-emerald-600 mt-1 uppercase">{{ $health['ssl_certificate'] }}</p>
        </div>
    </div>

    <!-- Configs Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Variable Environment Terenkripsi (Production Secrets)</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Target Env</th>
                    <th class="py-3 px-4">Group</th>
                    <th class="py-3 px-4">Key Config</th>
                    <th class="py-3 px-4">Value Terenkripsi</th>
                    <th class="py-3 px-4">Diperbarui Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($configs as $cfg)
                <tr>
                    <td class="py-3 px-4 uppercase font-bold text-emerald-600">{{ $cfg->environment }}</td>
                    <td class="py-3 px-4 text-xs font-semibold uppercase">{{ $cfg->config_group }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-gray-900 dark:text-white">{{ $cfg->config_key }}</td>
                    <td class="py-3 px-4 font-mono text-xs text-gray-400">•••••••••••••••••••• (Encrypted Payload)</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $cfg->last_updated_by }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada variabel environment yang didaftarkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
