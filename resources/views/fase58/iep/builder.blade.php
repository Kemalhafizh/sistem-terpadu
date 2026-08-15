@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Builder IEP
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang - Pembuatan Dokumen IEP</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ isset($iep) ? route('iep.update', $iep) : route('iep.store') }}" method="POST">
            @csrf
            @if(isset($iep)) @method('PUT') @endif
            
            <!-- form fields would go here... for demonstration, adding basic placeholders -->
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300">Tingkat Kinerja Saat Ini</label>
                <textarea name="present_level_of_performance" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ $iep->present_level_of_performance ?? '' }}</textarea>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-bold hover:bg-emerald-700">
                    Simpan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
