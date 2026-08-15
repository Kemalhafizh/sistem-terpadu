@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Perencana Diferensiasi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ isset($plan) ? route('differentiation.update', $plan) : route('differentiation.store') }}" method="POST">
            @csrf
            @if(isset($plan)) @method('PUT') @endif
            <!-- Form fields for differentiation -->
            <div class="flex justify-end mt-4">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-bold hover:bg-emerald-700">
                    Simpan Perencanaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
