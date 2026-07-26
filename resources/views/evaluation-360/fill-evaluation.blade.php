@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Kuesioner Penilaian Evaluasi 360° & EDOM</h1>
        <p class="text-xs text-gray-500 mb-6">Siklus: {{ $cycle->title }} (Data masukan dijaga kerahasiaannya)</p>

        @if(session('success'))
        <div class="p-4 mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('hc.evaluation.submit', $cycle->id) }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Guru yang Dinilai (Evaluatee)</label>
                <select name="evaluatee_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }} (Guru Mata Pelajaran)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Perspektif Evaluator</label>
                <select name="evaluator_type" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="supervisor">Kepala Sekolah / Supervisor</option>
                    <option value="peer">Rekan Sejawat Guru (Peer)</option>
                    <option value="student" selected>Siswa (EDOM)</option>
                    <option value="parent">Orang Tua / Wali Murid</option>
                    <option value="self">Self-Assessment Mandiri</option>
                </select>
            </div>

            <div class="space-y-4 pt-2">
                <p class="text-xs font-bold text-gray-400 uppercase">Indikator Komposisi Penilaian Kinerja (Skala Likert 1 - 5):</p>
                
                @php
                    $indicators = [
                        'Penguasaan materi & kedalaman ilmu pedagogik',
                        'Kedisiplinan, kehadiran, & ketepatan waktu masuk kelas',
                        'Kemampuan komunikasi interaktif & empati kepada siswa',
                        'Penggunaan inovasi media e-learning & teknologi',
                        'Keteladanan sikap islami & akhlak mahmudah',
                    ];
                @endphp

                @foreach($indicators as $idx => $ind)
                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $idx + 1 }}. {{ $ind }}</span>
                    <div class="flex gap-2">
                        @for($s = 1; $s <= 5; $s++)
                        <label class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-gray-800 border hover:border-sky-500 cursor-pointer font-bold text-xs text-gray-700 dark:text-gray-300">
                            <input type="radio" name="scores[ind{{ $idx }}]" value="{{ $s }}" required class="sr-only">
                            {{ $s }}
                        </label>
                        @endfor
                    </div>
                </div>
                @endforeach
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Masukan & Catatan Konstruktif (Opsional)</label>
                <textarea name="comments" rows="3" placeholder="Sangat menginspirasi dan menguasai teknik pembelajaran interaktif..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('hc.evaluation.cycles') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-sky-600 hover:bg-sky-700 text-white rounded-xl font-bold text-xs shadow-md">Kirim Penilaian 360°</button>
            </div>
        </form>
    </div>
</div>
@endsection
