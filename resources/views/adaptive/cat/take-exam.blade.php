@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Ujian Adaptif CAT - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <div class="mb-4">
            <span class="text-sm font-bold text-gray-600 dark:text-gray-300">Kemajuan Ujian: Soal ke-5</span>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2">
                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 45%"></div>
            </div>
        </div>
        
        <h2 class="font-bold text-gray-900 dark:text-white mb-4 text-lg">Pertanyaan:</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">[Isi Pertanyaan]</p>
        
        <div class="space-y-2">
            <button class="w-full text-left px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">A. Opsi A</button>
            <button class="w-full text-left px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">B. Opsi B</button>
        </div>
    </div>
</div>
@endsection
