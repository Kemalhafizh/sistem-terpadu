@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h9m-9 0V9a2 2 0 012-2h2a2 2 0 012 2v12m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Multi-Year Academic Planning & Graduation Pathway Tracker
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sistem Pelacakan Peminatan Akademik (IPA, IPS, Bahasa, Keagamaan) & Kesiapan Kelulusan</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('academic.pathways.graduation_readiness') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">
                Checklist Kesiapan Kelulusan
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Enroll Pathway -->
    @if(isset($students))
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Daftarkan Peminatan Akademik Siswa</h3>
        <form action="{{ route('academic.pathways.store_enrollment') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Siswa</label>
                <select name="student_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($students as $st)
                    <option value="{{ $st->id }}">{{ $st->name }} (Kelas X / XI)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilihan Peminatan (Pathway)</label>
                <select name="academic_pathway_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
                    @foreach($pathways as $pw)
                    <option value="{{ $pw->id }}">{{ $pw->name }} (Min. {{ $pw->required_credits }} SKS)</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Daftarkan Peminatan</button>
            </div>
        </form>
    </div>
    @endif

    <!-- Pathways Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($pathways as $pw)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <span class="px-2.5 py-1 text-xs font-black rounded-lg uppercase bg-indigo-100 text-indigo-700">
                    {{ $pw->name }}
                </span>
                <span class="text-xs font-bold text-gray-400">Target: {{ $pw->required_credits }} SKS Kredit</span>
            </div>
            <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">{{ $pw->description }}</p>
            <div class="border-t pt-3 border-gray-100 flex justify-between items-center text-xs font-bold">
                <span class="text-indigo-600">{{ $pw->enrollments_count ?? 0 }} Siswa Terdaftar</span>
            </div>
        </div>
        @empty
        <div class="col-span-2 py-12 text-center text-gray-400">Belum ada jalur peminatan yang dikonfigurasi.</div>
        @endforelse
    </div>
</div>
@endsection
