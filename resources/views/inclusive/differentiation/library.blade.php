@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            Perpustakaan Diferensiasi
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6">
            <div class="flex justify-end mb-4">
                <a href="{{ route('differentiation.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">Rencana Baru</a>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipe Diferensiasi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    @forelse($plans ?? [] as $plan)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($plan->differentiation_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('differentiation.show', $plan) }}" class="text-indigo-600 hover:text-indigo-900">Lihat</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center">Belum ada data rencana.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
