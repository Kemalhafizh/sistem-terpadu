@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">{{ __('Buat RKAS Baru') }}</h1>
        <p class="text-xs text-gray-500 mb-6">Rencana Anggaran Pendapatan dan Belanja Sekolah {{ school_name() }}</p>

        <form action="{{ route('financial.budget.store') }}" method="POST" class="space-y-4">
            @csrf
            <p class="text-xs text-gray-500">{{ __('Tulis 2026/2027 untuk tahun ajaran (Juli–Juni) atau 2026 untuk tahun kalender (Januari–Desember). Realisasi dari buku besar dihitung dalam rentang itu.') }}</p>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ __('Tahun Anggaran') }}</label>
                <input type="text" name="fiscal_year" value="{{ old('fiscal_year') }}" placeholder="2026/2027" pattern="\d{4}(/\d{4})?" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Dokumen RAPBS</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="RAPBS SMA ISA 1 Semarang TA 2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white">
            </div>

            <p class="text-xs text-gray-500">{{ __('Total pendapatan dan belanja dihitung dari pos anggaran yang Anda tambahkan setelah ini.') }}</p>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('financial.budget.dashboard') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md">{{ __('Simpan RKAS') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
