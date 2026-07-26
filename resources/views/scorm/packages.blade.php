@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                SCORM / xAPI Interoperability Learning Packages
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Interoperabilitas Konten E-Learning SCORM 1.2 / 2004 & xAPI LRS Lite SMA ISA 1 Semarang</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Upload SCORM Package -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Unggah / Registrasi Konten Paket SCORM Baru</h3>
        <form action="{{ route('academic.scorm.store_package') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Modul SCORM</label>
                <input type="text" name="title" placeholder="Modul Interaktif Eksperimen Fisika Optik" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Versi SCORM / xAPI</label>
                <select name="version" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="scorm12">SCORM 1.2 Standard</option>
                    <option value="scorm2004">SCORM 2004 4th Edition</option>
                    <option value="xapi">xAPI (Tin Can API)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">File Path / Entry Point</label>
                <input type="text" name="file_path" value="storage/scorm/physics_optics_v1.zip" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold text-xs shadow-md">+ Unggah Paket SCORM</button>
            </div>
        </form>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($packages as $pkg)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-black rounded-lg uppercase bg-purple-100 text-purple-700">
                    {{ strtoupper($pkg->version) }}
                </span>
                <span class="text-xs font-bold text-gray-400">By {{ $pkg->uploader->name }}</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $pkg->title }}</h3>
            <div class="border-t pt-3 border-gray-100 flex justify-between items-center">
                <span class="text-xs font-bold text-emerald-600">Status: {{ strtoupper($pkg->status) }}</span>
                <a href="{{ route('academic.scorm.launch', $pkg->id) }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow">Putar Konten SCORM &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada paket SCORM yang diunggah.</div>
        @endforelse
    </div>
</div>
@endsection
