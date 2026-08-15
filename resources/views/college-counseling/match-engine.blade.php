@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex items-center space-x-4 mb-8">
        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">College Match Engine - SMA Islam Sultan Agung 1 Semarang</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Rekomendasi Universitas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Universitas</th>
                        <th scope="col" class="px-6 py-3">Jurusan</th>
                        <th scope="col" class="px-6 py-3">Probabilitas Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4">Universitas Diponegoro</td>
                        <td class="px-6 py-4">Teknik Informatika</td>
                        <td class="px-6 py-4"><span class="bg-emerald-100 text-emerald-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded dark:bg-emerald-200 dark:text-emerald-900">Tinggi</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
