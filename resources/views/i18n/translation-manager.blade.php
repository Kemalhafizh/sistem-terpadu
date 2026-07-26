@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                Advanced i18n & Full Arabic (RTL) Support Manager
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sistem Manajemen Terjemahan 3 Bahasa (ID, EN, AR) & Tata Letak RTL Kalender Hijriah</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('ux.i18n.report') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">
                Laporan Kelengkapan Terjemahan
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Override Translation -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Override Teks Terjemahan (Kamus Bahasa)</h3>
        <form action="{{ route('ux.i18n.override') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Bahasa (Locale)</label>
                <select name="locale" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="id">Bahasa Indonesia (ID)</option>
                    <option value="en">English (EN)</option>
                    <option value="ar" selected>العربية - Bahasa Arab RTL (AR)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Translation Key</label>
                <input type="text" name="key" placeholder="dashboard.welcome_title" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nilai Terjemahan (Value)</label>
                <input type="text" name="value" placeholder="مرحباً بكم في نظام المدرسة" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Simpan Terjemahan Custom</button>
            </div>
        </form>
    </div>

    <!-- Translations Grid -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Translation Overrides Active</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Locale</th>
                    <th class="py-3 px-4">Key</th>
                    <th class="py-3 px-4">Nilai Terjemahan (Value)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($overrides as $ov)
                <tr>
                    <td class="py-3 px-4 uppercase font-bold text-indigo-600">{{ $ov->locale }}</td>
                    <td class="py-3 px-4 font-mono text-xs font-bold">{{ $ov->key }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white" dir="{{ $ov->locale === 'ar' ? 'rtl' : 'ltr' }}">{{ $ov->value }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-6 text-center text-gray-400">Belum ada custom translation override.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
