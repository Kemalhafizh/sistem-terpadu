@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Panduan Pendampingan - {{ $task->student->name ?? 'Siswa' }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Catatan Panduan Orang Tua</h2>
        <div class="prose dark:prose-invert max-w-none text-sm text-gray-600 dark:text-gray-300">
            {{ $task->parent_guide_notes }}
        </div>
        <div class="mt-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                Estimasi Waktu: {{ $task->estimated_parent_time_minutes }} Menit
            </span>
        </div>
    </div>

    @if(!$task->parent_reviewed)
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <form action="{{ route('homework_copilot.mark_reviewed', $task->id) }}" method="POST">
            @csrf
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm transition-colors w-full">
                Tandai Sudah Mendampingi
            </button>
        </form>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <div class="mb-4 inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="font-bold">Telah didampingi</span>
        </div>
        <form action="{{ route('homework_copilot.submit_feedback', $task->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="feedback" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Masukan untuk Guru (Opsional)</label>
                <textarea name="feedback" id="feedback" rows="4" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $task->parent_feedback }}</textarea>
            </div>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm transition-colors">
                Kirim Masukan
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
