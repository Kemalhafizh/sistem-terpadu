@extends('layouts.app')
@section('content')
<div class="p-6">
<h1 class="text-2xl font-black text-gray-900 dark:text-white">
<svg class="w-6 h-6 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
{{ school_name() }} - Artikel</h1>
<div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
<div class="rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-4 bg-white dark:bg-gray-800">
<h2 class="font-bold text-gray-800 dark:text-gray-200">Judul Artikel</h2>
<p class="text-sm text-gray-600 dark:text-gray-300 divide-y divide-gray-150">Konten singkat...</p>
</div>
</div>
</div>
@endsection