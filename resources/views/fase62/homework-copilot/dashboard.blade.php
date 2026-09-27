@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Homework Copilot - {{ school_name() }}
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">Panduan pendampingan belajar di rumah untuk orang tua.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Daftar Tugas Pending</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Siswa</th>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tugas</th>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Estimasi Waktu</th>
                            <th class="px-6 py-3 text-left font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($tasks ?? [] as $task)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $task->student->name ?? 'Siswa' }}</td>
                            <td class="px-6 py-4">Panduan Belajar #{{ $task->id }}</td>
                            <td class="px-6 py-4">{{ $task->estimated_parent_time_minutes }} Menit</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('homework_copilot.task_guide', $task->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Panduan</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Tidak ada tugas pendampingan saat ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
