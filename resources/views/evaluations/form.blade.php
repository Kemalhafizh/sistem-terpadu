@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Kuesioner Evaluasi Pembelajaran (Anonim)</h1>
            <p class="text-xs text-gray-500 mt-1">Mapel: <strong>{{ $evaluation->subject->name ?? 'Mapel' }}</strong> | Guru: <strong>{{ $evaluation->teacher->name ?? 'Guru' }}</strong></p>
        </div>
        <a href="{{ route('evaluations.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <form action="{{ route('evaluations.store_response') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="evaluation_id" value="{{ $evaluation->id }}">

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Penilaian Rating (1 - 5 Bintang)</label>
                <select name="rating" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                    <option value="5">5 - Sangat Memuaskan (Penjelasan Jelas & Menginspirasi)</option>
                    <option value="4">4 - Memuaskan (Penjelasan Sangat Baik)</option>
                    <option value="3">3 - Cukup (Penjelasan Cukup Jelas)</option>
                    <option value="2">2 - Kurang (Perlu Peningkatan Metode Pembelajaran)</option>
                    <option value="1">1 - Sangat Kurang</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Komentar & Masukan Konstruktif</label>
                <textarea name="comments" rows="3" placeholder="Saran perbaikan metode mengajar atau media pembelajaran..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-pink-600 hover:bg-pink-700 text-white rounded-xl font-bold text-xs shadow-md">+ Kirim Evaluasi Anonim</button>
            </div>
        </form>
    </div>
</div>
@endsection
