@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-2">Input Asesmen Karakter & Merit Points</h1>
        <p class="text-xs text-gray-500 mb-6">Formulir evaluasi karakter Profil Pelajar Pancasila & Akhlak Mahmudah</p>

        @if(session('success'))
        <div class="p-4 mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('growth.character.store_assessment') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Siswa</label>
                <select name="student_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    @foreach($students as $st)
                    <option value="{{ $st->id }}">{{ $st->name }} (Kelas X / XI)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dimensi Karakter</label>
                <select name="character_dimension_id" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    @foreach($dimensions as $dim)
                    <option value="{{ $dim->id }}">{{ $dim->name }} ({{ strtoupper($dim->category) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Skor Capaian Karakter</label>
                <select name="score" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-4 py-2.5 text-sm font-semibold">
                    <option value="4">4 - Sangat Baik (SB)</option>
                    <option value="3" selected>3 - Baik (B)</option>
                    <option value="2">2 - Cukup (C)</option>
                    <option value="1">1 - Perlu Bimbingan (PB)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Catatan Bukti Perilaku (Evidence Notes)</label>
                <textarea name="evidence_notes" rows="3" placeholder="Siswa aktif membantu kegiatan bakti sosial dan taat beribadah..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>

            <div class="pt-4 flex justify-end gap-2">
                <a href="{{ route('growth.character.dimensions') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-xs shadow-md">Simpan Asesmen Karakter</button>
            </div>
        </form>
    </div>
</div>
@endsection
