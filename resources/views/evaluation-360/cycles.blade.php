@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                360-Degree Teacher Evaluation & EDOM
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sistem Evaluasi Kinerja Guru Holistik (Supervisor, Peer, EDOM Siswa, Orang Tua, Self) SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
@if(\App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'hc.evaluation.ranking'))
                        <a href="{{ route('hc.evaluation.ranking') }}" class="px-4 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs shadow-md">
                Peringkat Kinerja Guru (Rank)
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Create Cycle -->
    @if(\App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'hc.evaluation.store_cycle'))
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Aktifkan Siklus Evaluasi 360° Baru</h3>
        <form action="{{ route('hc.evaluation.store_cycle') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Siklus Evaluasi Kinerja</label>
                <input type="text" name="title" placeholder="Evaluasi Kinerja Guru & EDOM Semester Ganjil 2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran</label>
                <input type="text" name="academic_year" value="2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs shadow-md">+ Generate Siklus & Instrumen Evaluasi</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Cycles Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($cycles as $cyc)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-sky-100 text-sky-700">
                    {{ strtoupper($cyc->status) }}
                </span>
                <span class="text-xs font-bold text-gray-400">{{ $cyc->academic_year }}</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $cyc->title }}</h3>
            <p class="text-xs text-gray-500">Periode: <strong>{{ $cyc->start_date->format('d M Y') }} - {{ $cyc->end_date->format('d M Y') }}</strong></p>
            <div class="border-t pt-3 border-gray-100 flex justify-between items-center">
                <span class="text-xs font-bold text-sky-600">{{ $cyc->instruments_count }} Instrumen Perspektif Active</span>
                <a href="{{ route('hc.evaluation.fill_form', $cyc->id) }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl shadow">Isi Form Evaluasi &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada siklus evaluasi 360° yang aktif.</div>
        @endforelse
    </div>
</div>
@endsection
