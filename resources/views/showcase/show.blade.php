@extends('layouts.app')

@section('content')
@php($author = $showcase->student)
<div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
    <a href="{{ route('showcase.gallery') }}" class="text-sm text-amber-700 dark:text-amber-300 hover:underline">&larr; Galeri Karya</a>

    <article class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm overflow-hidden">
        @if($showcase->cover_url)
            <img src="{{ $showcase->cover_url }}" alt="Sampul {{ $showcase->project_title }}" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ $showcase->project_title }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                        Oleh:
                        @if($author)
                            <a href="{{ route('showcase.profile', $author) }}" class="font-semibold hover:underline">{{ $author->name }}</a>@if($author->student?->classroom) &bull; {{ $author->student->classroom->name }}@endif
                        @else
                            Siswa
                        @endif
                        &bull; {{ $showcase->created_at->translatedFormat('d M Y') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $showcase->view_count }} kali dilihat &bull; {{ $showcase->endorsements->count() }} dukungan
                        @unless($showcase->is_public) &bull; <span class="font-bold">Privat</span>@endunless
                    </p>
                </div>
                @if($canEdit)
                    <div class="flex gap-2">
                        <a href="{{ route('showcase.edit', $showcase) }}" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-sm font-bold">Ubah</a>
                        <form method="POST" action="{{ route('showcase.destroy', $showcase) }}" onsubmit="return confirm('Hapus karya ini beserta dukungannya?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-4 py-2 rounded-xl border border-rose-300 text-rose-600 text-sm font-bold">Hapus</button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-200 whitespace-pre-line">{{ $showcase->description }}</div>

            @if(!empty($showcase->media_urls))
                <div>
                    <h2 class="text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Tautan karya</h2>
                    <ul class="space-y-1">
                        @foreach($showcase->media_urls as $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-sm text-amber-700 dark:text-amber-300 hover:underline break-all">{{ $url }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </article>

    <section class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h2 class="text-lg font-black text-gray-900 dark:text-white">Dukungan ({{ $showcase->endorsements->count() }})</h2>

        @if($canEndorse)
            <form method="POST" action="{{ route('showcase.endorse', $showcase) }}" class="space-y-2">
                @csrf
                <textarea name="endorsement_text" rows="3" maxlength="500" required placeholder="Tulis apresiasi atau masukan untuk karya ini" class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">{{ old('endorsement_text') }}</textarea>
                @error('endorsement_text')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold">Beri Dukungan</button>
            </form>
        @elseif($alreadyEndorsed)
            <p class="text-sm text-emerald-700 dark:text-emerald-300">Anda sudah memberi dukungan untuk karya ini.</p>
        @endif

        @forelse($showcase->endorsements as $endorsement)
            <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
                <p class="text-sm text-gray-700 dark:text-gray-200">{{ $endorsement->endorsement_text }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $endorsement->endorser?->name ?? 'Pengguna' }} &bull; {{ $endorsement->created_at->translatedFormat('d M Y') }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada dukungan.</p>
        @endforelse
    </section>
</div>
@endsection
