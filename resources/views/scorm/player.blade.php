@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">SCORM Interactive Player: {{ $package->title }}</h1>
            <p class="text-xs text-gray-500 mt-1">Status Pembelajaran: <strong class="text-purple-600 uppercase font-bold text-sm" id="scorm-status">{{ $tracking->lesson_status }}</strong> | Skor: <strong class="text-emerald-600 font-bold" id="scorm-score">{{ $tracking->score_raw ?? 0 }}</strong></p>
        </div>
        <div class="flex gap-2">
            <button onclick="finishScormLesson()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow">Selesaikan Modul & Sync LRS</button>
            <a href="{{ route('academic.scorm.packages') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
        </div>
    </div>

    <!-- Player Iframe Container -->
    <div class="bg-slate-900 rounded-3xl overflow-hidden border border-slate-700 shadow-2xl p-2 min-h-[500px] flex items-center justify-center relative">
        <div class="text-center text-white space-y-4">
            <svg class="w-16 h-16 text-purple-400 mx-auto animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-lg font-black">[SCORM Player Simulated Standalone Sandbox]</h3>
            <p class="text-xs text-slate-400">Konten E-Learning Interaktif: {{ $package->entry_point }}</p>
        </div>
    </div>
</div>

<script>
function finishScormLesson() {
    fetch('{{ route("academic.scorm.record_tracking", $package->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            lesson_status: 'passed',
            score_raw: 95
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('scorm-status').innerText = 'PASSED';
        document.getElementById('scorm-score').innerText = '95';
        alert('Modul SCORM Berhasil Diselesaikan! LRS Statement tersinkronisasi.');
    });
}
</script>
@endsection
