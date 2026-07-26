@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Scholarship & Grant Management Portal
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portal Pengelolaan Beasiswa PIP, BOS, & Beasiswa Internal Yayasan YBWSA</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('financial.scholarships.create_program') }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">
                + Buat Program Beasiswa
            </a>
            <a href="{{ route('financial.scholarships.report') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">
                Laporan Penyaluran
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Programs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($programs as $prog)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                        {{ $prog->type === 'pip' ? 'bg-blue-100 text-blue-700' : ($prog->type === 'internal_merit' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700') }}">
                        {{ strtoupper($prog->type) }}
                    </span>
                    <span class="text-xs font-bold text-gray-400">{{ $prog->academic_year }}</span>
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $prog->name }}</h3>
                <p class="text-xs text-gray-500">Sumber Dana: <strong class="uppercase text-gray-700 dark:text-gray-300">{{ $prog->source }}</strong></p>
                <div class="space-y-1 text-sm border-t pt-3 border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs font-semibold">Besaran / Siswa:</span>
                        <span class="font-bold text-emerald-600">Rp {{ number_format($prog->amount_per_student, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs font-semibold">Kuota Penerima:</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $prog->total_quota }} Siswa</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs font-semibold">Jumlah Pendaftar:</span>
                        <span class="font-bold text-indigo-600">{{ $prog->applications_count }} Pendaftar</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                @if($prog->status === 'draft')
                <form action="{{ route('financial.scholarships.open', $prog->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-xl shadow">Buka Pendaftaran</button>
                </form>
                @else
                <a href="{{ route('financial.scholarships.apply_form', $prog->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">+ Daftar Siswa</a>
                @endif
                <a href="{{ route('financial.scholarships.review', $prog->id) }}" class="text-xs font-bold text-gray-600 dark:text-gray-400 hover:underline">Seleksi & Pencairan &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-400">Belum ada program beasiswa yang aktif.</div>
        @endforelse
    </div>
</div>
@endsection
