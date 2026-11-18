@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Character Report Card P5: {{ $student->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Total Merit Points Karakter: <strong class="text-amber-500 text-sm">{{ $radar['total_merit_points'] }} Poin</strong></p>
        </div>
        <a href="{{ route('growth.character.dimensions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- Dimensions Radar / List View -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($radar['dimensions'] as $dim)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold uppercase text-amber-500">{{ $dim['category'] }}</span>
                <span class="px-2.5 py-1 text-xs font-black rounded-lg bg-amber-100 text-amber-700">
                    {{ $dim['predicate'] }}
                </span>
            </div>
            <h4 class="text-base font-black text-gray-900 dark:text-white">{{ $dim['name'] }}</h4>
            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                <div class="bg-amber-500 h-2.5 rounded-full" style="width: {{ ($dim['score'] / 4) * 100 }}%"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
