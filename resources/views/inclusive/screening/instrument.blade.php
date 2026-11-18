@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Instrumen Skrining
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('screening.store') }}" method="POST">
            @csrf
            <!-- Instrument fields -->
            <div class="flex justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">Simpan Hasil</button>
            </div>
        </form>
    </div>
</div>
@endsection
