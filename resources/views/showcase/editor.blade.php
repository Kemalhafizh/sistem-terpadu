@extends('layouts.app')

@section('content')
@php
    $editing = $showcase->exists;
    $links = old('media_urls', $showcase->media_urls ?? []);
    $links = array_pad(array_slice($links, 0, 5), 3, '');
    $input = 'w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm';
@endphp
<div class="max-w-3xl mx-auto py-10 sm:px-6 lg:px-8 space-y-6">
    <div class="flex items-center space-x-4">
        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ $editing ? 'Ubah Karya' : 'Unggah Karya Baru' }}</h1>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl border border-rose-300 bg-rose-50 text-rose-700 text-sm">
            <p class="font-bold">Karya belum tersimpan:</p>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('showcase.update', $showcase) : route('showcase.store') }}"
          class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-5">
        @csrf
        @if($editing) @method('PUT') @endif

        <div>
            <label for="project_title" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Judul karya</label>
            <input id="project_title" name="project_title" type="text" maxlength="255" required value="{{ old('project_title', $showcase->project_title) }}" class="{{ $input }}">
        </div>

        <div>
            <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Deskripsi</label>
            <textarea id="description" name="description" rows="6" maxlength="5000" required class="{{ $input }}">{{ old('description', $showcase->description) }}</textarea>
        </div>

        <div>
            <label for="cover_image" class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Gambar sampul (opsional, JPG/PNG/WebP, maks. 5 MB)</label>
            @if($editing && $showcase->cover_url)
                <img src="{{ $showcase->cover_url }}" alt="Sampul saat ini" class="h-32 rounded-xl mb-2 object-cover">
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 mb-2">
                    <input type="checkbox" name="remove_cover" value="1" class="rounded"> Hapus gambar sampul
                </label>
            @endif
            <input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" class="block text-sm">
        </div>

        <div>
            <p class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-1">Tautan karya (opsional: video, repositori, dokumen)</p>
            <div class="space-y-2">
                @foreach($links as $i => $link)
                    <input name="media_urls[]" type="url" value="{{ $link }}" placeholder="https://…" aria-label="Tautan karya {{ $i + 1 }}" class="{{ $input }}">
                @endforeach
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
            <input type="hidden" name="is_public" value="0">
            <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $showcase->is_public)) class="rounded">
            Tampilkan di galeri (tanpa centang: hanya Anda dan guru yang dapat melihat)
        </label>

        <div class="flex justify-between items-center pt-2">
            <a href="{{ $editing ? route('showcase.show', $showcase) : route('showcase.my') }}" class="text-sm text-gray-500 hover:underline">Batal</a>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold">
                {{ $editing ? 'Simpan Perubahan' : 'Simpan Karya' }}
            </button>
        </div>
    </form>
</div>
@endsection
