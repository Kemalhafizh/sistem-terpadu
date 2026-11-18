@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Dimensi Karakter P5 & Akhlak Mahmudah
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">6 Dimensi Profil Pelajar Pancasila & 5 Dimensi Karakter Islami SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('growth.character.assess_form') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md">
                + Input Asesmen Karakter
            </a>
            <a href="{{ route('growth.character.leaderboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">
                Papan Merit Points
            </a>
        </div>
    </div>

    <!-- Dimensions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($dimensions as $dim)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                    {{ $dim->category === 'p5' ? 'bg-amber-100 text-amber-700' : 'bg-teal-100 text-teal-700' }}">
                    {{ strtoupper($dim->category) }}
                </span>
                <span class="text-xs font-bold text-gray-400">Order #{{ $dim->display_order }}</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $dim->name }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">{{ $dim->description }}</p>
        </div>
        @endforeach
    </div>
</div>
@endsection
