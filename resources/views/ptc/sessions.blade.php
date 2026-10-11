@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Parent-Teacher Conference (PTC) Scheduler
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Penjadwalan Mandiri Konsultasi Wali Murid & Guru SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
@if(\App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'growth.ptc.teacher_schedule'))
                        <a href="{{ route('growth.ptc.teacher_schedule') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">
                Jadwal Konsultasi Saya (Guru)
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Create Session -->
    @if(\App\Support\Access\RouteAccess::allowsRoute(auth()->user(), 'growth.ptc.store_session'))
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Buat Sesi PTC Baru (Wali Kelas / Sekolah)</h3>
        <form action="{{ route('growth.ptc.store_session') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Sesi Consultative</label>
                <input type="text" name="title" placeholder="Konsultasi Rapor Mid Semester Ganjil 2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran</label>
                <input type="text" name="academic_year" value="2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Pelaksanaan</label>
                <input type="date" name="date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jam Mulai</label>
                <input type="time" name="start_time" value="08:00" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jam Selesai</label>
                <input type="time" name="end_time" value="12:00" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Generate Sesi & Slot PTC</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Sessions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($sessions as $s)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-indigo-100 text-indigo-700">
                    {{ $s->status }}
                </span>
                <span class="text-xs font-bold text-gray-400">{{ $s->date->format('d M Y') }}</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $s->title }}</h3>
            <p class="text-xs text-gray-500">Waktu: <strong>{{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }} WIB</strong> (Durasi Slot: {{ $s->slot_duration_minutes }} menit)</p>
            <div class="border-t pt-3 border-gray-100 flex justify-between items-center">
                <span class="text-xs font-bold text-indigo-600">{{ $s->slots_count }} Total Slot Tergenerasi</span>
                <a href="{{ route('growth.ptc.available_slots', $s->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow">Pilih Slot Orang Tua &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada sesi PTC yang terjadwal.</div>
        @endforelse
    </div>
</div>
@endsection
