@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Buat Kiriman Kelas
        </h1>
    </div>

    <form action="{{ route('class_feed.store_post') }}" method="POST" class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        @csrf
        <div class="space-y-6">
            <div>
                <label for="post_type" class="block text-sm font-bold text-gray-700 dark:text-gray-300">Jenis Kiriman</label>
                <select id="post_type" name="post_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl">
                    <option value="general_update">Update Umum</option>
                    <option value="behavior_note">Catatan Perilaku</option>
                    <option value="achievement_highlight">Sorotan Pencapaian</option>
                </select>
            </div>

            <div>
                <label for="content" class="block text-sm font-bold text-gray-700 dark:text-gray-300">Isi Kiriman</label>
                <textarea id="content" name="content" rows="6" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Tuliskan perkembangan, kegiatan, atau pengumuman kelas..."></textarea>
            </div>

            <div class="flex items-center">
                <input id="requires_acknowledgment" name="requires_acknowledgment" type="checkbox" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                <label for="requires_acknowledgment" class="ml-2 block text-sm text-gray-900 dark:text-gray-300 font-bold">
                    Wajibkan konfirmasi dari orang tua
                </label>
            </div>

            <div class="pt-4 flex justify-end">
                <a href="{{ route('class_feed.timeline') }}" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                    Batal
                </a>
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Kirim Sekarang
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
