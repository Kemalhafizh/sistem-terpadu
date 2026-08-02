@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Predictive Student Performance Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Prediksi Dini Performa Akademik & Identifikasi Siswa Berisiko SMA ISA 1 Semarang</p>
        </div>
    </div>

    <!-- Predictions Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Hasil Prediksi ML Performa Siswa</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Proyeksi IPK / GPA</th>
                    <th class="py-3 px-4">Tingkat Risiko</th>
                    <th class="py-3 px-4">Tanggal Prediksi</th>
                    <th class="py-3 px-4 text-right">Aksi BK</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($predictions as $pred)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $pred->student->name ?? 'N/A' }}</td>
                    <td class="py-3 px-4 font-black text-indigo-600">{{ $pred->predicted_gpa }} / 4.00</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase
                            {{ $pred->risk_level === 'high' ? 'bg-rose-100 text-rose-700' : ($pred->risk_level === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ strtoupper($pred->risk_level) }} RISK
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $pred->prediction_date ? $pred->prediction_date->format('d M Y') : '-' }}</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('analytics.predictive.student_risk', $pred->id) }}" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold">Faktor Risiko &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada data prediksi performa siswa.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
