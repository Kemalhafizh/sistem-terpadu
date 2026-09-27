@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            Linimasa Kelas - {{ school_name() }}
        </h1>
    </div>

    <div class="space-y-6 max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-4 mb-8">
            <a href="{{ route('class_feed.post_creator') }}" class="block w-full py-3 px-4 bg-gray-50 dark:bg-gray-700 rounded-xl text-left text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                Bagikan momen atau pengumuman ke orang tua...
            </a>
        </div>

        @forelse($posts ?? [] as $post)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                        {{ substr($post->teacher->name ?? 'G', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">{{ $post->teacher->name ?? 'Guru' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $post->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @if($post->post_type === 'achievement_highlight')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Pencapaian</span>
                @elseif($post->post_type === 'behavior_note')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">Catatan Perilaku</span>
                @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">Update Umum</span>
                @endif
            </div>

            <div class="prose dark:prose-invert max-w-none text-sm text-gray-700 dark:text-gray-300 mb-4">
                {{ $post->content }}
            </div>

            @if($post->requires_acknowledgment)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <form action="{{ route('class_feed.acknowledge_post', $post->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Konfirmasi Membaca
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
            Belum ada kiriman di linimasa.
        </div>
        @endforelse
    </div>
</div>
@endsection
