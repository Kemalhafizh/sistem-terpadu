@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Workspace Mentoring Alumni & Siswa</h1>
            <p class="text-xs text-gray-500 mt-1">Area Bimbingan Karir & Diskusi Persiapan Kuliah</p>
        </div>
        <a href="{{ route('mentorship.directory') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase">Sesi Mentoring Aktif</h3>
        <p class="text-xs text-gray-500">Workspace bimbingan intensif alumni dan siswa SMA ISA 1 Semarang.</p>
    </div>
</div>
@endsection
