@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Private Mood Journal Siswa</h1>
        <p class="text-xs text-gray-500 mb-6">Catat kondisi perasaan dan refleksi harian Anda (Dijamin 100% Rahasia)</p>

        <!-- Form Mood -->
        <form action="{{ route('growth.wellbeing.store_mood_journal') }}" method="POST" class="space-y-4 bg-rose-50/50 dark:bg-rose-950/20 p-5 rounded-2xl border border-rose-100">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Bagaimana perasaanmu hari ini?</label>
                <div class="flex justify-between items-center gap-2">
                    <label class="flex-1 text-center p-3 rounded-xl bg-white dark:bg-gray-800 border hover:border-rose-400 cursor-pointer">
                        <input type="radio" name="mood_level" value="1" required class="sr-only">
                        <span class="block text-xl">Sangat Buruk</span>
                        <span class="text-[10px] font-bold text-gray-400">Level 1</span>
                    </label>
                    <label class="flex-1 text-center p-3 rounded-xl bg-white dark:bg-gray-800 border hover:border-rose-400 cursor-pointer">
                        <input type="radio" name="mood_level" value="2" required class="sr-only">
                        <span class="block text-xl">Kurang Baik</span>
                        <span class="text-[10px] font-bold text-gray-400">Level 2</span>
                    </label>
                    <label class="flex-1 text-center p-3 rounded-xl bg-white dark:bg-gray-800 border hover:border-rose-400 cursor-pointer">
                        <input type="radio" name="mood_level" value="3" value="3" checked required class="sr-only">
                        <span class="block text-xl">Biasa Saja</span>
                        <span class="text-[10px] font-bold text-gray-400">Level 3</span>
                    </label>
                    <label class="flex-1 text-center p-3 rounded-xl bg-white dark:bg-gray-800 border hover:border-rose-400 cursor-pointer">
                        <input type="radio" name="mood_level" value="4" required class="sr-only">
                        <span class="block text-xl">Senang</span>
                        <span class="text-[10px] font-bold text-gray-400">Level 4</span>
                    </label>
                    <label class="flex-1 text-center p-3 rounded-xl bg-white dark:bg-gray-800 border hover:border-rose-400 cursor-pointer">
                        <input type="radio" name="mood_level" value="5" required class="sr-only">
                        <span class="block text-xl">Sangat Bahagia</span>
                        <span class="text-[10px] font-bold text-gray-400">Level 5</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi Tidur Semalam (Jam)</label>
                <input type="number" step="0.5" name="sleep_hours" value="7.5" class="w-full bg-white dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Refleksi / Catatan Jurnal Hari Ini</label>
                <textarea name="journal_entry" rows="3" placeholder="Apa hal baik atau tantangan yang kamu rasakan hari ini?..." class="w-full bg-white dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md">Simpan Catatan Mood</button>
            </div>
        </form>

        <!-- History -->
        <div class="space-y-3 pt-4">
            <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase">Riwayat Jurnal Mood</h3>
            @forelse($journals as $j)
            <div class="p-4 bg-white dark:bg-gray-800 border border-gray-100 rounded-2xl space-y-1">
                <div class="flex justify-between items-center text-xs">
                    <span class="font-bold text-rose-600">Level Mood: {{ $j->mood_level }}/5</span>
                    <span class="text-gray-400">{{ $j->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($j->journal_entry)
                <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $j->journal_entry }}</p>
                @endif
                <div class="text-[11px] text-gray-400">Tidur: {{ $j->sleep_hours ?? '-' }} Jam</div>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">Belum ada jurnal mood yang dicatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
