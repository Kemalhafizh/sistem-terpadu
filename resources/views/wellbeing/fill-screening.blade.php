@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        @if(isset($screening))
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">{{ $screening->title }}</h1>
        <p class="text-xs text-gray-500 mb-6">Instrumen Skrining Mandiri (Jawaban Anda dijamin rahasia & terlindungi)</p>

        <form action="{{ route('growth.wellbeing.submit', $screening->id) }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-4">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-200">Apakah Anda merasakan gejala berikut dalam 30 hari terakhir?</p>
                
                @php
                    $questions = [
                        'Sering merasa sakit kepala / pusing?',
                        'Kurang nafsu makan atau makan tidak teratur?',
                        'Tidur tidak nyenyak atau sering terbangun?',
                        'Mudah merasa takut, cemas, atau khawatir?',
                        'Tangan gemetar atau merasa tegang?',
                        'Sulit berpikir jernih atau fokus belajar?',
                        'Merasa tidak bahagia atau sedih berkepanjangan?',
                        'Kehilangan minat pada hobi / aktivitas sekolah?',
                    ];
                @endphp

                @foreach($questions as $idx => $q)
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $idx + 1 }}. {{ $q }}</span>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-1 text-xs font-bold text-gray-600">
                            <input type="radio" name="responses[q{{ $idx }}]" value="1" required class="text-rose-600"> Ya
                        </label>
                        <label class="flex items-center gap-1 text-xs font-bold text-gray-600">
                            <input type="radio" name="responses[q{{ $idx }}]" value="0" required class="text-rose-600"> Tidak
                        </label>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('growth.wellbeing.screenings') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md">Kirim Respon Skrining</button>
            </div>
        </form>
        @else
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Buat Sesi Skrining Mental Baru</h1>
        <form action="{{ route('growth.wellbeing.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Skrining</label>
                <input type="text" name="title" placeholder="Skrining Kesehatan Emosional Semester Ganjil 2026" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Instrumen</label>
                    <select name="screening_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                        <option value="srq20">SRQ-20 (Self-Reporting Questionnaire)</option>
                        <option value="phq9">PHQ-9 (Patient Health Questionnaire)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Ajaran</label>
                    <input type="text" name="academic_year" value="2026/2027" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                </div>
            </div>
            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('growth.wellbeing.screenings') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan & Aktifkan</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
