@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
    <div class="flex items-center space-x-4">
        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ $user->name }}</h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $user->student?->classroom?->name ?? 'Kelas belum tercatat' }} &bull; {{ school_name() }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $totals['entries'] }} karya dipublikasikan &bull; {{ $totals['endorsements'] }} dukungan diterima</p>
        </div>
    </div>

    @if($showcases->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-10 text-center text-gray-600 dark:text-gray-300">
            Siswa ini belum mempublikasikan karya.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($showcases as $showcase)
                @include('showcase._card', ['showcase' => $showcase, 'hideAuthor' => true])
            @endforeach
        </div>
        {{ $showcases->links() }}
    @endif
</div>
@endsection
