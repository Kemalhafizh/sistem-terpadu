@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Tambah Environment Secret Variable Terenkripsi</h1>
        <p class="text-xs text-gray-500 mb-6">Variabel akan dienkripsi dengan AES-256-CBC secara otomatis sebelum disimpan ke database</p>

        <form action="{{ route('deploy.environment.store_config') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Environment</label>
                <select name="environment" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="production" selected>Production Server</option>
                    <option value="staging">Staging Server</option>
                    <option value="development">Development Local</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Group Config</label>
                <select name="config_group" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="database">Database & Redis Connection</option>
                    <option value="api_keys">External API Keys (WhatsApp, Payment Gateway)</option>
                    <option value="mail">Mail & SMTP Configuration</option>
                    <option value="security">Application Security Secret</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Config Key</label>
                <input type="text" name="config_key" placeholder="WHATSAPP_API_SECRET_TOKEN" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Config Value (Rahasia)</label>
                <input type="password" name="config_value" placeholder="sk_live_998342100438192..." required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('deploy.environment.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan & Enkripsi Secret</button>
            </div>
        </form>
    </div>
</div>
@endsection
