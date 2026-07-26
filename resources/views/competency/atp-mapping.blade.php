@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Alur Tujuan Pembelajaran (ATP): {{ $objective->tp_code }}</h1>
            <p class="text-xs text-gray-500 mt-1">Mata Pelajaran: {{ $objective->outcome->subject->name }} | {{ $objective->tp_description }}</p>
        </div>
        <a href="{{ route('academic.competency.outcomes') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Form Add ATP -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Map Alur Tujuan Pembelajaran (ATP Mingguan)</h3>
        <form action="{{ route('academic.competency.store_atp', $objective->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Minggu Ke-</label>
                <input type="number" name="week_number" min="1" max="20" value="1" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jam Pelajaran (JP)</label>
                <input type="number" name="teaching_hours" min="1" value="2" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Metode Asesmen</label>
                <input type="text" name="assessment_method" placeholder="Asesmen Formatif Kuis / Unjuk Kerja" required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl px-3 py-2 text-sm font-semibold">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Aktivitas Pembelajaran Pembuka - Inti - Penutup</label>
                <textarea name="learning_activities" rows="3" placeholder="Diskusi kelompok pemecahan masalah eksponen dengan pendekatan Problem-Based Learning..." required class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold"></textarea>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-xs shadow-md">+ Petakan Minggu ATP</button>
            </div>
        </form>
    </div>

    <!-- Mappings Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Minggu Ke-</th>
                    <th class="py-3 px-4">Beban JP</th>
                    <th class="py-3 px-4">Aktivitas Pembelajaran</th>
                    <th class="py-3 px-4">Metode Asesmen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($objective->atpMappings as $atp)
                <tr>
                    <td class="py-3 px-4 font-bold text-teal-600">Minggu #{{ $atp->week_number }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $atp->teaching_hours }} JP</td>
                    <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200">{{ $atp->learning_activities }}</td>
                    <td class="py-3 px-4 text-xs font-bold text-indigo-600">{{ $atp->assessment_method }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada alur mingguan ATP yang dipetakan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
