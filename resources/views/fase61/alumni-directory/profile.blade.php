@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Profil Alumni</h1>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-300 font-bold mb-4">SMA Islam Sultan Agung 1 Semarang</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    <tr>
                        <td class="px-6 py-4">Informasi Profil Alumni belum tersedia.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
