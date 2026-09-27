@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex items-center space-x-4 mb-8">
        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Portal Magang - {{ school_name() }}</h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Daftar Lowongan</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Perusahaan</th>
                        <th scope="col" class="px-6 py-3">Sektor</th>
                        <th scope="col" class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="px-6 py-4">PT Teknologi Nusantara</td>
                        <td class="px-6 py-4">IT</td>
                        <td class="px-6 py-4"><button class="text-indigo-600 hover:text-indigo-900">Daftar</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
