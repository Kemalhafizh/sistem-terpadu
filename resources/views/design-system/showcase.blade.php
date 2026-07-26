@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Enterprise Design System Component Library Showcase
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Galeri Komponen Blade Terstandarisasi Platform SISTA SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('ux.design_system.documentation') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Dokumentasi Kit &rarr;</a>
    </div>

    <!-- Component Gallery Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stat Cards -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase">Category: Layout & Stat Card</span>
            <div class="p-4 bg-purple-50 dark:bg-purple-950/20 rounded-xl border border-purple-100">
                <span class="text-xs font-bold text-purple-700 uppercase">Total Siswa Aktif</span>
                <p class="text-2xl font-black text-purple-900 dark:text-purple-100">1,248 Siswa</p>
            </div>
        </div>

        <!-- Badges & Status -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase">Category: Status Badges</span>
            <div class="flex flex-wrap gap-2 pt-2">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-xs rounded-full">ACTIVE</span>
                <span class="px-3 py-1 bg-rose-100 text-rose-800 font-bold text-xs rounded-full">CRITICAL</span>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 font-bold text-xs rounded-full">VERIFIED</span>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 font-bold text-xs rounded-full">PENDING</span>
            </div>
        </div>

        <!-- Buttons Kit -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm space-y-2">
            <span class="text-xs font-bold text-gray-400 uppercase">Category: Action Buttons</span>
            <div class="flex flex-wrap gap-2 pt-2">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow-md">Primary Button</button>
                <button class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold shadow-md">Success Action</button>
            </div>
        </div>
    </div>
</div>
@endsection
