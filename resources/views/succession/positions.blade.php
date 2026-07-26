@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h9m-9 0V9a2 2 0 012-2h2a2 2 0 012 2v12m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                School Leadership Succession Planning & Talent Pipeline
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perencanaan & Pemetaan Suksesi Pimpinan Eksekutif Sekolah (Kepala Sekolah & Waka) SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('hc.succession.pipeline_report') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md">
                Laporan Talent Pipeline
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Add Candidate -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Nominasikan Kandidat Suksesi Pimpinan Baru</h3>
        <form action="{{ route('hc.succession.store_candidate') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Posisi Kepemimpinan</label>
                <select name="leadership_position_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($positions as $pos)
                    <option value="{{ $pos->id }}">{{ strtoupper(str_replace('_', ' ', $pos->title)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Guru Kandidat</label>
                <select name="candidate_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tingkat Kesiapan (Readiness)</label>
                <select name="readiness_level" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="ready_now">Ready Now (Siap Sekarang)</option>
                    <option value="ready_1yr" selected>Ready 1 Year (Siap 1 Tahun)</option>
                    <option value="ready_3yr">Ready 3 Years (Siap 3 Tahun)</option>
                    <option value="developing">Developing (Dalam Pembinaan)</option>
                </select>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md">+ Nominasikan Kandidat Pimpinan</button>
            </div>
        </form>
    </div>

    <!-- Positions Grid / Org Chart -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($positions as $p)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-black rounded-lg uppercase bg-amber-100 text-amber-700">
                    {{ strtoupper(str_replace('_', ' ', $p->title)) }}
                </span>
                <span class="text-xs font-bold text-rose-500 uppercase">Prioritas: {{ $p->succession_priority }}</span>
            </div>
            <p class="text-xs text-gray-500">Pejabat Saat Ini: <strong>{{ $p->currentHolder->name ?? 'Belum Terdefinisi' }}</strong></p>
            
            <div class="border-t pt-3 border-gray-100 space-y-2">
                <h4 class="text-xs font-black text-gray-400 uppercase">Kandidat Suksesi Pipeline:</h4>
                <div class="space-y-1">
                    @foreach($p->candidates as $cand)
                    <div class="p-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl flex justify-between items-center text-xs">
                        <span class="font-bold text-gray-900 dark:text-white">{{ $cand->candidate->name }}</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold uppercase text-[10px]">{{ str_replace('_', ' ', $cand->readiness_level) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada posisi kepemimpinan sekolah yang terdaftar.</div>
        @endforelse
    </div>
</div>
@endsection
