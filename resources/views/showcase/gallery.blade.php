@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">Galeri Karya Siswa - {{ school_name() }}</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('showcase.my') }}" class="px-4 py-2 rounded-xl border border-amber-600 text-amber-700 dark:text-amber-300 text-sm font-bold">Karya Saya</a>
            @can('create', App\Models\StudentShowcase::class)
                <a href="{{ route('showcase.create') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold">Unggah Karya</a>
            @endcan
        </div>
    </div>

    <form method="GET" action="{{ route('showcase.gallery') }}" class="flex gap-2">
        <input type="search" name="q" value="{{ $search }}" placeholder="Cari judul, deskripsi, atau nama siswa" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
        <button type="submit" class="px-4 py-2 rounded-xl bg-gray-800 dark:bg-gray-700 text-white text-sm font-bold">Cari</button>
    </form>

    @if($showcases->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-10 text-center text-gray-600 dark:text-gray-300">
            @if($search !== '')
                Tidak ada karya yang cocok dengan "{{ $search }}".
            @else
                Belum ada karya siswa yang dipublikasikan.
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($showcases as $showcase)
                @include('showcase._card', ['showcase' => $showcase])
            @endforeach
        </div>
        {{ $showcases->links() }}
    @endif
</div>
@endsection
