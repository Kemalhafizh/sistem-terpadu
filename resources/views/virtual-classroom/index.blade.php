@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Virtual Classroom & Video Conferencing Hub
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portal Rapat Pembelajaran Tatap Muka Online (Google Meet, Zoom, Jitsi Meet)</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Schedule Meeting -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Jadwalkan Rapat Kelas Virtual Baru</h3>
        <form action="{{ route('academic.virtual.store_meeting') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Pembelajaran / Sesi</label>
                <input type="text" name="title" placeholder="Kelas Virtual Fisika - Gelombang Elektromagnetik" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Video Provider</label>
                <select name="provider" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="google_meet">Google Meet (Recommended)</option>
                    <option value="zoom">Zoom Video Conference</option>
                    <option value="jitsi">Jitsi Meet (Open Source)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Mulai</label>
                <input type="datetime-local" name="start_time" value="{{ date('Y-m-d\TH:i', strtotime('+1 hour')) }}" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi (Menit)</label>
                <input type="number" name="duration_minutes" value="60" min="15" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-2 flex items-end">
                <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs shadow-md">+ Jadwalkan Kelas Virtual</button>
            </div>
        </form>
    </div>

    <!-- Meetings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($meetings as $mt)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-black rounded-lg uppercase bg-blue-100 text-blue-700">
                    {{ strtoupper($mt->provider) }}
                </span>
                <span class="text-xs font-bold text-gray-400">{{ $mt->start_time->format('d M Y H:i') }} WIB</span>
            </div>
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $mt->title }}</h3>
            <p class="text-xs text-gray-500">Host: <strong>{{ $mt->host->name }}</strong> | Durasi: {{ $mt->duration_minutes }} Menit</p>
            <div class="border-t pt-3 border-gray-100 flex justify-between items-center">
                <span class="text-xs font-bold text-emerald-600">{{ $mt->attendees_count }} Peserta Hadir</span>
                <a href="{{ route('academic.virtual.join_lobby', $mt->id) }}" target="_blank" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow">Join Lobby Kelas &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada kelas virtual yang dijadwalkan.</div>
        @endforelse
    </div>
</div>
@endsection
