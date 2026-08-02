@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Alumni Mentorship Program Platform
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Direktori Mentor Alumni SMA ISA 1 Semarang untuk Bimbingan Karir & Studi Lanjut</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($mentors as $mtr)
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center font-black text-lg">
                    {{ substr($mtr->alumni->name ?? 'A', 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $mtr->alumni->name ?? 'Alumni' }}</h3>
                    <p class="text-xs text-sky-600 font-semibold">Mentor Alumni</p>
                </div>
            </div>
            <p class="text-xs text-gray-500 line-clamp-2">{{ $mtr->bio ?? 'Siap memberikan bimbingan persiapan masuk PTN & karir teknologi.' }}</p>
            <div class="pt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs">
                <span class="text-gray-400">Kapasitas: {{ $mtr->max_mentees }} Mentee</span>
                <a href="#" class="px-3 py-1.5 bg-sky-600 text-white rounded-xl font-bold">Ajukan Mentoring &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-150 dark:border-gray-700 text-center text-gray-400">
            Belum ada mentor alumni yang terdaftar saat ini.
        </div>
        @endforelse
    </div>
</div>
@endsection
