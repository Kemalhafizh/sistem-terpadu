@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Kurikulum Merdeka Competency Mapping (CP / TP / ATP)
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Arsitektur Pedagogis Capaian & Tujuan Pembelajaran Fase E & F SMA ISA 1 Semarang</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Add Outcome CP -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Tambah Capaian Pembelajaran (CP) Baru</h3>
        <form action="{{ route('academic.competency.store_outcome') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($subjects as $subj)
                    <option value="{{ $subj->id }}">{{ $subj->name }} ({{ $subj->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Fase Kurikulum</label>
                <select name="phase" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    <option value="E">Fase E (Kelas X)</option>
                    <option value="F">Fase F (Kelas XI - XII)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kode CP (Unique)</label>
                <input type="text" name="cp_code" placeholder="CP-BIO-E01" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Elemen CP</label>
                <input type="text" name="element" placeholder="Pemahaman Biologi / Keterampilan Proses" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deskripsi Capaian Pembelajaran (CP)</label>
                <input type="text" name="cp_description" placeholder="Peserta didik mampu menggeneralisasi sifat-sifat eksponen dan logaritma..." required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md">+ Simpan Capaian Pembelajaran (CP)</button>
            </div>
        </form>
    </div>

    <!-- Outcomes Tree List -->
    <div class="space-y-4">
        @forelse($outcomes as $oc)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-lg uppercase bg-teal-100 text-teal-700 font-mono">
                        {{ $oc->cp_code }} (Fase {{ $oc->phase }})
                    </span>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white mt-1">{{ $oc->subject->name }} — Elemen: {{ $oc->element }}</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 font-medium">{{ $oc->cp_description }}</p>
                </div>
            </div>

            <!-- Objectives List -->
            <div class="border-t pt-3 border-gray-100 dark:border-gray-700 space-y-2">
                <h4 class="text-xs font-black text-gray-500 uppercase">Tujuan Pembelajaran (TP) Terkait:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($oc->objectives as $tp)
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl space-y-1">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-teal-600">{{ $tp->tp_code }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-100 text-indigo-700 uppercase">Bloom: {{ $tp->bloom_taxonomy_level }}</span>
                        </div>
                        <p class="text-xs text-gray-800 dark:text-gray-200 font-medium">{{ $tp->tp_description }}</p>
                        <div class="pt-1 flex justify-end">
                            <a href="{{ route('academic.competency.atp_mapping', $tp->id) }}" class="text-[11px] font-bold text-teal-600 hover:underline">Pemetaan ATP Mingguan &rarr;</a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Add TP Form Inline -->
                <form action="{{ route('academic.competency.store_objective', $oc->id) }}" method="POST" class="pt-2 flex gap-2">
                    @csrf
                    <input type="text" name="tp_code" placeholder="TP-01" required class="bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold w-24">
                    <input type="text" name="tp_description" placeholder="Deskripsi Tujuan Pembelajaran (TP)..." required class="bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-lg px-3 py-1.5 text-xs font-semibold flex-1">
                    <select name="bloom_taxonomy_level" class="bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-semibold">
                        <option value="remember">Remember (C1)</option>
                        <option value="understand">Understand (C2)</option>
                        <option value="apply" selected>Apply (C3)</option>
                        <option value="analyze">Analyze (C4)</option>
                        <option value="evaluate">Evaluate (C5)</option>
                        <option value="create">Create (C6)</option>
                    </select>
                    <button type="submit" class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-xs font-bold shadow">+ Tambah TP</button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-gray-400 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150">Belum ada Capaian Pembelajaran (CP) yang dipetakan.</div>
        @endforelse
    </div>
</div>
@endsection
