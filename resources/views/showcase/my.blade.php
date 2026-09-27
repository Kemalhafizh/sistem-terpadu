@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Karya Saya</h1>
        <div class="flex gap-2">
            <a href="{{ route('showcase.gallery') }}" class="px-4 py-2 rounded-xl border border-amber-600 text-amber-700 dark:text-amber-300 text-sm font-bold">Galeri</a>
            @if($canCreate)
                <a href="{{ route('showcase.create') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold">Unggah Karya</a>
            @endif
        </div>
    </div>

    @if($showcases->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-10 text-center text-gray-600 dark:text-gray-300">
            @if($canCreate)
                Anda belum mengunggah karya.
            @else
                Galeri karya hanya dapat diisi oleh akun siswa.
            @endif
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
