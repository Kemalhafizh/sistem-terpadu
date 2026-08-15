@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Direktori Keahlian Orang Tua
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">SMA Islam Sultan Agung 1 Semarang</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($skills ?? [] as $skill)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $skill->parent_name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $skill->profession }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold text-xl">
                    {{ substr($skill->parent_name, 0, 1) }}
                </div>
            </div>
            
            <div class="mt-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($skill->skill_tags ?? [] as $tag)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                        {{ $tag }}
                    </span>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex gap-4">
                @if($skill->available_for_guest_lecture)
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Kuliah Tamu
                </span>
                @endif
                @if($skill->available_for_mentorship)
                <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Mentoring
                </span>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            <h3 class="mt-2 text-sm font-bold text-gray-900 dark:text-white">Belum Ada Data</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada profil keahlian orang tua yang terdaftar.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
