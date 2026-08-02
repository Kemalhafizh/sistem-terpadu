@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Detail Faktor Risiko Akademik Siswa</h1>
            <p class="text-xs text-gray-500 mt-1">Siswa: <strong>{{ $prediction->student->name ?? 'Siswa' }}</strong> | Proyeksi IPK: <strong class="text-indigo-600 font-bold text-sm">{{ $prediction->predicted_gpa }}</strong></p>
        </div>
        <a href="{{ route('analytics.predictive.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Intervention -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Catat Tindakan Intervensi Konselor BK</h3>
        <form action="{{ route('analytics.predictive.store_intervention') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="prediction_id" value="{{ $prediction->id }}">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tindakan Intervensi / Rencana Bimbingan</label>
                <textarea name="action_taken" rows="3" required placeholder="Bimbingan konseling perorangan & pemanggilan orang tua..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md">+ Simpan Intervensi BK</button>
            </div>
        </form>
    </div>

    <!-- History Interventions -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Riwayat Intervensi yang Diberikan</h3>
        <div class="space-y-2">
            @forelse($prediction->interventions as $inv)
            <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-xl text-xs space-y-1">
                <div class="flex justify-between items-center">
                    <span class="font-bold text-gray-900 dark:text-white">Konselor: {{ $inv->counselor->name ?? 'Guru BK' }}</span>
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 font-bold uppercase rounded text-[10px]">{{ $inv->status }}</span>
                </div>
                <p class="text-gray-600 dark:text-gray-300">{{ $inv->action_taken }}</p>
            </div>
            @empty
            <p class="text-center text-gray-400 py-4 text-xs">Belum ada tindakan intervensi yang dicatat.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
