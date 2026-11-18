@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Peringatan Guru (Akomodasi)
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 text-gray-600 dark:text-gray-300">
        <!-- Alerts output loop -->
        <p>Memuat peringatan terkait akomodasi siswa yang memerlukan perhatian khusus di kelas...</p>
    </div>
</div>
@endsection
